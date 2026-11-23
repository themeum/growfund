<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * @since 1.1.2
 */
class Translator {
    /**
     * Get the path and url of the plugin that owns the given text domain.
     *
     * @param string $domain
     * @return array{path: string, url: string}
     */
    protected static function get_domain_plugin(string $domain): array
    {
        if ($domain === 'growfund-pro' && defined('GROWFUND_PRO_DIR_PATH')) {
            return ['path' => GROWFUND_PRO_DIR_PATH, 'url' => GROWFUND_PRO_DIR_URL];
        }

        return ['path' => GROWFUND_DIR_PATH, 'url' => GROWFUND_DIR_URL];
    }

    /**
     * Modified time of the translation file WordPress uses for the domain (.l10n.php on WP >= 6.5, otherwise .mo), 0 if there is none.
     * Cheap on purpose: it does not load/parse the translations.
     * Expects the plugin's languages folder to be registered already (see load_js_translations()).
     *
     * @param string $domain
     * @param string $locale
     * @return int
     */
    protected static function get_translation_source_mtime(string $domain, string $locale): int
    {
        // Use the global registry: the plugin's languages folder registered by load_plugin_textdomain() lives only there.
        global $wp_textdomain_registry;

        if (!$wp_textdomain_registry instanceof \WP_Textdomain_Registry) {
            return 0;
        }

        $dir = $wp_textdomain_registry->get($domain, $locale);
        $filesystem = growfund_get_wp_filesystem();

        if (!$dir || !$filesystem) {
            return 0;
        }

        // WP >= 6.5 loads .l10n.php first (falls back to .mo); older versions only read .mo.
        $extensions = class_exists('\WP_Translation_Controller', false) ? ['.l10n.php', '.mo'] : ['.mo'];

        foreach ($extensions as $extension) {
            $source = trailingslashit($dir) . "{$domain}-{$locale}{$extension}";

            if (file_exists($source)) {
                return (int) $filesystem->mtime($source);
            }
        }

        return 0;
    }

    /**
     * @param string $domain
     * @param string $locale
     * @return string The JS that registers the translations with wp.i18n, or an empty string if there are none.
     */
    protected static function build_set_locale_data_script(string $domain, string $locale): string
    {
        $locale_data = static::get_locale_data($domain, $locale);

        if (empty($locale_data)) {
            return '';
        }

        return sprintf(
            'window.wp && window.wp.i18n && window.wp.i18n.setLocaleData(%s, %s);',
            // Keep non-ASCII (e.g. Bangla) characters as-is instead of 6-byte \uXXXX escapes to keep the file small.
            // Escape < > & so translated strings can't affect HTML parsing when printed inline in a <script> tag.
            wp_json_encode($locale_data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP),
            wp_json_encode($domain)
        );
    }

    /**
     * Write a file through the WP_Filesystem API: write a temp file in the same folder, then move it over the target,
     * so a half-written file is never served. Creates the directory if needed.
     *
     * @param string $file
     * @param string $contents
     * @param int $mtime Optional modified time to set on the file (0 = leave as is).
     * @return bool
     */
    protected static function write_language_file(string $file, string $contents, int $mtime = 0): bool
    {
        $filesystem = growfund_get_wp_filesystem();

        $dir = dirname($file);

        // The helper only returns a direct filesystem (FTP/SSH map paths differently and are too slow for a page load).
        // The temp file is created in, and moved within, this folder, so the folder itself must be writable.
        if (!$filesystem || !wp_mkdir_p($dir) || !$filesystem->is_writable($dir)) {
            return false;
        }

        $temp = $file . '.' . wp_generate_password(8, false) . '.tmp';

        // Set the mtime on the temp file before the move: the move keeps it, so the final file appears with the right mtime at once.
        $written = growfund_file_put_contents($temp, $contents)
            && (!$mtime || $filesystem->touch($temp, $mtime))
            && $filesystem->move($temp, $file, true);

        // Always clean up the temp file, whether the write succeeded or failed at any step.
        if ($filesystem->exists($temp)) {
            $filesystem->delete($temp);
        }

        if ($written) {
            // move() falls back to copy() when rename() fails, and copy() doesn't keep the mtime: pin it again,
            // otherwise the mtime check in load_js_translations() would trigger a rebuild on every request.
            if ($mtime && (int) $filesystem->mtime($file) !== $mtime) {
                $filesystem->touch($file, $mtime);
            }

            static::remove_old_language_files($file);
        }

        return $written;
    }

    /**
     * Keep only one file per locale. The new file has the same name as the old one ({domain}-{locale}.js),
     * so the move in write_language_file() already replaced the old file of this locale. What can be left over
     * are temp files of this locale ({domain}-{locale}.js.{random}.tmp) from requests that died mid-write.
     * Only temp files older than a minute are removed, so a concurrent request's in-flight write is not deleted.
     * Files of other locales (and other domains) are not touched.
     *
     * @param string $file The freshly written file.
     * @return void
     */
    protected static function remove_old_language_files(string $file): void
    {
        $filesystem = growfund_get_wp_filesystem();
        $leftovers = glob($file . '.*.tmp');

        if (!$filesystem || empty($leftovers)) {
            return;
        }

        $stale_before = time() - MINUTE_IN_SECONDS;

        foreach ($leftovers as $leftover) {
            if ((int) $filesystem->mtime($leftover) < $stale_before) {
                $filesystem->delete($leftover);
            }
        }
    }

    /**
     * Build Jed locale data for a text domain from the loaded PHP translations (.mo / .l10n.php),
     * regardless of where they live (plugin languages folder or wp-content/languages/plugins, e.g. Loco Translate).
     *
     * @param string $domain
     * @param string $locale
     * @return array
     */
    protected static function get_locale_data(string $domain, string $locale): array
    {
        // The languages folder is registered in load_js_translations(); make sure the translations are actually loaded.
        _load_textdomain_just_in_time($domain);

        // Entries come from WordPress in the gettext .mo format:
        //  - key:   "msgid", or "context\4msgid" when the string has a context (\4 = 0x04 separator between context and msgid)
        //  - value: the translation; plural forms of one string are joined into a single string by "\0" (NUL byte),
        //           e.g. "1 campaign\0%d campaigns".
        // wp.i18n (Jed) expects each value as an array of plural forms, so we explode on "\0" at the end.
        if (class_exists('\WP_Translation_Controller', false)) { 
            // Used for WP >= 6.5
            $controller = \WP_Translation_Controller::get_instance();
            $entries = $controller->get_entries($domain);
            $headers = $controller->get_headers($domain);
        } else {
            // Used for WP < 6.5
            $mo = get_translations_for_domain($domain);
            // Old WP: entries are Translation_Entry objects (already keyed by "context\4msgid", plural forms in an array).
            // Join the plural forms with "\0" so both branches share the same format as the WP >= 6.5 controller.
            $entries = array_map(static fn ($entry) => implode("\0", $entry->translations), $mo->entries ?? []);
            $headers = $mo->headers ?? [];
        }

        if (empty($entries)) {
            return [];
        }

        $meta = array_filter([
            'domain' => $domain,
            'lang' => $locale,
            'plural-forms' => $headers['Plural-Forms'] ?? '',
        ]);

        // "" key = Jed meta (domain, lang, plural-forms). Split "a\0b" => ["a", "b"] for each translation.
        return ['' => $meta] + array_map(static fn ($translation) => explode("\0", (string) $translation), $entries);
    }

    /**
     * Load the JavaScript translations for the growfund plugin.
     *
     * The translations are written once to a static JS file (uploads/growfund/i18n/{domain}-{locale}.js) and enqueued
     * as a normal script, so the browser can cache it. The file is rebuilt whenever the .mo file changes.
     * If the file can't be written, the translations are printed inline instead.
     *
     * @param string $domain The domain of the translations.
     * @param string $handle The script handle that needs the translations.
     * @return void
     */
    public static function load_js_translations(string $domain = 'growfund', string $handle = 'growfund-bundle')
    {
        if (!wp_script_is($handle, 'enqueued')) {
            return;
        }

        $locale = determine_locale();

        // The locale becomes part of a file path: only allow plain locale names (defence in depth).
        if ($locale === 'en_US' || !preg_match('/^[A-Za-z0-9_-]+$/', $locale)) {
            return;
        }

        $plugin = static::get_domain_plugin($domain);

        // Register the languages folder once; WP then also checks WP_LANG_DIR/plugins (where Loco Translate saves).
        load_plugin_textdomain($domain, false, basename(untrailingslashit($plugin['path'])) . '/languages');

        // Print the translations inline when the file can't be used: WP < 6.1 has no text domain registry to locate
        // the translation file, and without direct file access (e.g. FTP/SSH) the file can't be checked or written.
        if (
            !class_exists('\WP_Textdomain_Registry', false)
            || version_compare(get_bloginfo('version'), '6.1', '<')
        ) {
            $script = static::build_set_locale_data_script($domain, $locale);

            if ($script !== '') {
                wp_add_inline_script($handle, $script, 'before');
            }

            return;
        }

        $source_version_mtime = static::get_translation_source_mtime($domain, $locale);
        $filesystem = growfund_get_wp_filesystem();

        if (!$source_version_mtime || !$filesystem) {
            return; // no translation file for this locale
        }

        // Stored in uploads (always writable, survives plugin updates) instead of the plugin folder.
        // Second arg false: don't create the year/month folder on every request.
        $uploads = wp_upload_dir(null, false);

        if (!empty($uploads['error'])) {
            return;
        }

        $relative_file = "growfund/i18n/{$domain}-{$locale}.js";
        $file = trailingslashit($uploads['basedir']) . $relative_file;

        // The generated file's mtime is pinned to the source's, so any change (newer or older) triggers a rebuild.
        // $filesystem is always available here (checked above).
        if (!$filesystem->exists($file) || (int) $filesystem->mtime($file) !== $source_version_mtime) {
            $script = static::build_set_locale_data_script($domain, $locale);

            if ($script === '') {
                return;
            }

            if (!static::write_language_file($file, $script, $source_version_mtime)) {
                // Not writable: fall back to printing the translations inline.
                wp_add_inline_script($handle, $script, 'before');
                return;
            }
        }

        $translation_handle = "{$handle}-i18n-{$domain}";

        wp_enqueue_script(
            $translation_handle,
            set_url_scheme(trailingslashit($uploads['baseurl']) . $relative_file),
            ['wp-i18n'],
            (string) $source_version_mtime,
            // Footer: the app bundles load there too. If the bundle is in <head> (e.g. the Vite dev app),
            // WordPress moves this dependency up to <head> automatically, so it still loads first.
            true
        );

        // Make sure the translations are loaded before the app bundle.
        $registered = wp_scripts()->registered[$handle] ?? null;

        if ($registered && !in_array($translation_handle, $registered->deps, true)) {
            $registered->deps[] = $translation_handle;
        }
    }

}