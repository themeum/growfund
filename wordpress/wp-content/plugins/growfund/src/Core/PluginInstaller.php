<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Exception;
use InvalidArgumentException;
use Plugin_Upgrader;
use Throwable;
use WP_Ajax_Upgrader_Skin;
use WP_Upgrader_Skin;


class PluginInstaller
{
    /**
     * The plugin upgrader client instance
     *
     * @var Plugin_Upgrader|null
     */
    protected $upgrader = null;

    /**
     * Initialize the plugin installer.
     *
     * @return void
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->prepare_environment();
        $this->create_upgrader_client();
    }

    /**
     * Install the plugin from the given url.
     *
     * @param string $plugin_url
     * @param boolean $activate
     * @return array
     * @since 1.0.0
     */
    public function install(string $plugin_url, bool $activate = true)
    {
        $this->check($plugin_url);

        $is_installed = $this->upgrader->install(
            $plugin_url,
            [
                'clear_update_cache' => true,
                'overwrite_package' => false,
            ]
        );

        if (is_wp_error($is_installed) || !$is_installed) {
            throw new Exception(
                esc_html__('The plugin installation failed.', 'growfund')
            );
        }

        $plugin_file = $this->upgrader->plugin_info();

        if (!$plugin_file) {
            throw new Exception(
                esc_html__('Could not retrieve the plugin information.', 'growfund')
            );
        }

        $response = [
            'installed' => true,
            'plugin' => $plugin_file,
            'activated' => false,
        ];

        if (!$activate) {
            return $response;
        }

        error_log('PluginInstaller: Attempting to activate plugin: ' . $plugin_file); // phpcs:ignore

        // Add extra safety checks before activation
        if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            error_log('PluginInstaller: Plugin file does not exist after installation'); // phpcs:ignore
            $response['activation_error'] = __('Plugin file not found after installation.', 'growfund');
            return $response;
        }

        // Check if plugin has any activation errors before activating
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
        if (empty($plugin_data['Name'])) {
            error_log('PluginInstaller: Invalid plugin data'); // phpcs:ignore
            $response['activation_error'] = __('Invalid plugin file.', 'growfund');
            return $response;
        }

        $is_activated = $this->activate($plugin_file);

        if (is_wp_error($is_activated) || !$is_activated) {
            $response['activated'] = false;
            $response['activation_error'] = __('Error activating the plugin.', 'growfund');

            return $response;
        }

        $response['activated'] = true;

        return $response;
    }

    /**
     * Upgrade the plugin from the given url.
     *
     * @param string $plugin_file
     * @param string|null $package_url
     * @param boolean $activate
     * @return array
     * @since 1.0.0
     */
    public function upgrade(string $plugin_file, $package_url = null, bool $activate = true)
    {
        if (empty($plugin_file)) {
            throw new InvalidArgumentException(
                esc_html__('The plugin file is required to upgrade the plugin.', 'growfund')
            );
        }

        $plugin_directory = WP_PLUGIN_DIR . '/' . $plugin_file;

        if (!file_exists($plugin_directory)) {
            throw new Exception(
                esc_html__('The plugin file does not exist and cannot be upgraded.', 'growfund')
            );
        }

        $was_active = is_plugin_active($plugin_file);

        $response = [
            'upgraded' => false,
            'plugin' => $plugin_file,
            'was_active' => $was_active,
            'activated' => false,
        ];

        try {
            if (!empty($package_url)) {
                $this->check($package_url);
                $is_upgraded = $this->upgrade_from_url($plugin_file, $package_url);
            } else {
                $is_upgraded = $this->upgrader->upgrade($plugin_file, [
                    'clear_update_cache' => true,
                ]);
            }

            if (is_wp_error($is_upgraded) || !$is_upgraded) {
                throw new Exception(
                    __('There was an error upgrading the plugin.', 'growfund')
                );
            }

            $response['upgraded'] = true;

            if (!$activate) {
                return $response;
            }

            $is_activated = $this->activate($plugin_file);

            if (is_wp_error($is_activated) || !$is_activated) {
                $response['activated'] = false;
            } else {
                $response['activated'] = true;
            }

            return $response;
        } catch (Throwable $error) {
            throw $error;
        }
    }

    /**
     * Check if the plugin is active.
     *
     * @param string $plugin_file
     * @return boolean
     */
    public function is_active(string $plugin_file)
    {
        return is_plugin_active($plugin_file);
    }

    /**
     * Check if the plugin is installed.
     *
     * @param string $plugin_file
     * @return boolean
     */
    public function is_installed(string $plugin_file)
    {
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_file;

        return file_exists($plugin_file);
    }

    /**
     * Upgrade the plugin from the given url.
     *
     * @param string $plugin_file
     * @param string $package_url
     * @return mixed
     * @since 1.0.0
     */
    protected function upgrade_from_url(string $plugin_file, string $package_url)
    {
        if (is_plugin_active($plugin_file)) {
            deactivate_plugins($plugin_file, true);
        }

        return $this->upgrader->install(
            $package_url,
            [
                'clear_update_cache' => true,
                'overwrite_package' => true,
            ]
        );
    }

    /**
     * Check the plugin url and validate it.
     *
     * @param string $plugin_url
     * @return void
     * @throws InvalidArgumentException
     * @since 1.0.0
     */
    protected function check(string $plugin_url)
    {
        if (empty($plugin_url)) {
            throw new InvalidArgumentException(
                sprintf(
                    /* translators: %s: Plugin URL */
                    esc_html__('The plugin URL is required to install the plugin. %s', 'growfund'),
                    esc_html($plugin_url)
                )
            );
        }

        if (!filter_var($plugin_url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf(
                    /* translators: %s: Plugin URL */
                    esc_html__('The plugin URL is invalid. %s', 'growfund'),
                    esc_html($plugin_url)
                )
            );
        }
    }

    /**
     * Activate the plugin.
     *
     * @param string $plugin
     * @return mixed
     * @since 1.0.0
     */
    protected function activate($plugin)
    {
        return activate_plugin($plugin);
    }


    /**
     * Prepare the environment for the plugin installer.
     *
     * @return void
     * @since 1.0.0
     */
    protected function prepare_environment()
    {
        if (!is_admin()) {
            define('WP_ADMIN', true);
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/template.php';
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';
        require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/screen.php';
    }

    /**
     * Create the upgrader client.
     *
     * @return void
     * @since 1.0.0
     */
    protected function create_upgrader_client()
    {
        $this->upgrader = new Plugin_Upgrader(
            new WP_Ajax_Upgrader_Skin()
        );
    }
}
