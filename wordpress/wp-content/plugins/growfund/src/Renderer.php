<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Exception;

class Renderer
{
    /**
     * Plugin's layout base path
     *
     * @var string|null
     */
    protected $plugin_template_base_path = null;

    /**
     * Active theme's layout base path
     *
     * @var string|null
     */
    protected $theme_template_base_path = null;

    /**
     * Renderer instance
     *
     * @var Renderer|null
     */
    protected static $instance = null;

    /**
     * Styles to be enqueued
     *
     * @var array
     */
    protected $styles = [];

    /**
     * Scripts to be enqueued
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * Get plugin's layout base path
     *
     * @return string
     */
    public function get_plugin_layout_base_path(): string
    {
        return $this->plugin_template_base_path;
    }

    /**
     * Set plugin's layout base path
     *
     * @param string $path
     * @return self
     */
    public function set_plugin_layout_base_path(string $base_path): self
    {
        $this->plugin_template_base_path = $base_path;

        return $this;
    }

    /**
     * Get active theme's layout base path
     *
     * @return string
     */
    public function get_theme_layout_base_path(): string
    {
        return $this->theme_template_base_path;
    }

    /**
     * Set active theme's layout base path
     *
     * @param string $theme_base_path
     * @return self
     */
    public function set_theme_layout_base_path(string $base_path): self
    {
        $this->theme_template_base_path = $base_path;

        return $this;
    }

    /**
     * Get HTML as string for a template with the given data.
     *
     * @param string $path
     * @param array $data
     * @return string
     */
    public function get_html(string $path, array $data = []): string
    {
        $template_path = $this->get_template_path($path);

        if (! file_exists($template_path)) {
            throw new Exception(esc_html__('Template not found', 'growfund'));
        }

        $this->enqueue_styles();
        $this->enqueue_scripts();

        $data = $this->process_nested_dtos($data);

        extract($data); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- this is intentional

        $output = '';

        ob_start();
        include $template_path;
        $output = ob_get_clean();

        return $output;
    }


    /**
     * Render a template with the given data.
     *
     * @param string $path
     * @param array $data
     */
    public function render(string $path, array $data = [])
    {
        $output = $this->get_html($path, $data);

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped  -- output is already escaped. this is intentional
    }

    /**
     * Process nested DTOs to ensure casting is applied before template rendering.
     * This method recursively traverses the data structure and triggers casting on DTOs
     * while properly handling nested objects, arrays, and primitives. DTOs are kept as 
     * objects for template access, and both DTOs and regular objects are processed 
     * recursively using get_object_vars() to find nested DTOs within their properties.
     *
     * @param mixed $data The data to process (can be array, DTO, object, or primitive)
     * @return mixed The processed data with all nested DTOs properly cast
     */
    protected function process_nested_dtos($data)
    {
        if (is_object($data) && $data instanceof DTO) {
            $data->get_values();

            $properties = get_object_vars($data);

            foreach ($properties as $key => $value) {
                $processed_value = $this->process_nested_dtos($value);
                $data->{$key} = $processed_value;
            }

            return $data;
        }

        if (is_object($data)) {
            $properties = get_object_vars($data);

            foreach ($properties as $key => $value) {
                $processed_value = $this->process_nested_dtos($value);
                $data->{$key} = $processed_value;
            }

            return $data;
        }

        if (is_array($data)) {
            $processed_data = [];

            foreach ($data as $key => $value) {
                $processed_data[$key] = $this->process_nested_dtos($value);
            }

            return $processed_data;
        }

        return $data;
    }

    /**
     * Load the template by it's path.
     *
     * @param string $path
     * @return string
     */
    public function get_path(string $path): string
    {
        return $this->get_template_path($path);
    }

    /**
     * Enqueue the registered styles.
     *
     * @return void
     */
    protected function enqueue_styles()
    {
        if (empty($this->styles)) {
            return;
        }

        foreach ($this->styles as $style) {
            $style_url = $this->get_style_url($style);
            wp_enqueue_style($style, $style_url, [], GROWFUND_VERSION);
        }

        $this->styles = [];
    }

    /**
     * Enqueue the registered scripts.
     *
     * @return void
     */
    protected function enqueue_scripts()
    {
        if (empty($this->scripts)) {
            return;
        }

        foreach ($this->scripts as $script) {
            $script_url = $this->get_script_url($script);
            wp_enqueue_script($script, $script_url, [], GROWFUND_VERSION, true);
        }

        $this->scripts = [];
    }

    /**
     * Add a style to be enqueued
     *
     * @param string $style
     * @return self
     */
    public function with_style(string $style): self
    {
        $this->styles[] = $style;

        return $this;
    }

    /**
     * Add a script to be enqueued
     *
     * @param string $script
     * @return self
     */
    public function with_script(string $script): self
    {
        $this->scripts[] = $script;

        return $this;
    }

    /**
     * Add styles to be enqueued
     *
     * @param array $styles
     * @return self
     */
    public function with_styles(array $styles): self
    {
        $this->styles = array_merge($this->styles, $styles);

        return $this;
    }

    /**
     * Add scripts to be enqueued
     *
     * @param array $scripts
     * @return self
     */
    public function with_scripts(array $scripts): self
    {
        $this->scripts = array_merge($this->scripts, $scripts);

        return $this;
    }

    /**
     * Parse a path
     *
     * @param string $path
     * @param string $extension
     * @return string
     */
    protected function parse(string $path, string $extension): string
    {
        $parts = explode('.', $path);
        $filename = array_pop($parts);

        if (empty($parts)) {
            return wp_normalize_path($filename . '.' . $extension);
        }

        $full_path = implode('/', $parts) . '/' . $filename . '.' . $extension;

        return wp_normalize_path($full_path);
    }

    /**
     * Parse a layout path
     *
     * @param string $path
     * @return string
     */
    protected function parse_template(string $path)
    {
        return $this->parse($path, 'php');
    }

    /**
     * Parse a style path
     *
     * @param string $path
     * @return string
     */
    protected function parse_style(string $path)
    {
        return $this->parse($path, 'css');
    }

    /**
     * Parse a script path
     *
     * @param string $path
     * @return string
     */
    protected function parse_script(string $path)
    {
        return $this->parse($path, 'js');
    }

    /**
     * Get the full path of a template, style, or script.
     *
     * @param string $path
     * @return string
     */
    protected function get_full_path(string $path): string
    {
        $plugin_layout_base_path = $this->get_plugin_layout_base_path();
        $theme_layout_base_path = $this->get_theme_layout_base_path();

        $plugin_layout_full_path = wp_normalize_path($plugin_layout_base_path . '/' . $path);
        $theme_layout_full_path = wp_normalize_path($theme_layout_base_path . '/' . $path);

        if (file_exists($theme_layout_full_path)) {
            return $theme_layout_full_path;
        }

        if (!file_exists($plugin_layout_full_path)) {
            /* translators: %s: the template path */
            throw new Exception(sprintf(esc_html__('Template not found: %s', 'growfund'), esc_html($plugin_layout_full_path)));
        }

        return $plugin_layout_full_path;
    }

    /**
     * Get a layout path.
     * e.g. 'templates.site.campaigns' to 'templates/site/campaigns.php'
     * It will also check if the layout exists in the active theme.
     * If override is found at active theme then the override will be used.
     * Otherwise, the layout will be loaded from the plugin's directory.
     *
     * @param string $path
     * @return string
     */
    public function get_template_path(string $path): string
    {
        return $this->get_full_path(
            $this->parse_template($path)
        );
    }

    /**
     * Get a style url.
     * e.g. 'styles.site.campaigns' to 'styles/site/campaigns.css'
     * It will also check if the style exists in the active theme.
     * If override is found at active theme then the override will be used.
     * Otherwise, the style will be loaded from the plugin's directory.
     *
     * @param string $path
     * @return string
     */
    protected function get_style_url(string $path): string
    {
        return $this->to_wp_content_url(
            $this->get_full_path(
                $this->parse_style($path)
            )
        );
    }

    /**
     * Get a script url.
     * e.g. 'scripts.site.campaigns' to 'scripts/site/campaigns.js'
     * It will also check if the script exists in the active theme.
     * If override is found at active theme then the override will be used.
     * Otherwise, the script will be loaded from the plugin's directory.
     *
     * @param string $path
     * @return string
     */
    protected function get_script_url(string $path): string
    {
        return $this->to_wp_content_url(
            $this->get_full_path(
                $this->parse_script($path)
            )
        );
    }

    /**
     * Convert a path to a WordPress content URL.
     * This will make the path relative to the WordPress content directory
     * and then convert it to a URL.
     *
     * @param string $path
     * @return string
     */
    protected function to_wp_content_url(string $path): string
    {
        $content_dir = wp_normalize_path(WP_CONTENT_DIR);
        $path = wp_normalize_path($path);

        $relative_path = str_replace($content_dir, '', $path);

        return content_url($relative_path);
    }
}
