<?php

namespace Growfund;

use Exception;
use Growfund\Constants\HookNames;
use Growfund\Core\AssetHandler;
use Growfund\Traits\Castable;

defined( 'ABSPATH' ) || exit;

/**
 * Base class for views
 * @since 1.0.4
 */
class View {
    use Castable;

    /**
     * Template directory
     * 
     * @var string
     */
    private $template_dir = '';

    /**
     * Template name
     * 
     * @var string
     */
    private $template = '';

    /**
     * Script action priority
     */
    private $script_priority = 30;

    /**
     * @var bool
     */
    private $is_admin_script = false;

    public function __construct(array $data = []) {
		foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $this->register_assets();
    }

    /**
     * Get base class name
     * 
     * @return string
     */
    private function get_base_class_name() {
        return basename(str_replace('\\', '/', static::class));
    }

    /**
     * Set template directory
     * @param string $template_dir
     * @return void
     */
    public function set_template_dir(string $template_dir) {
        $this->template_dir = $template_dir;
    }

    /**
     * Get template directory
     * 
     * @return string
     */
    protected function get_template_dir() {
        return $this->template_dir;
    }

    /**
     * Set template directory
     * @param string $template
     * @return void
     */
    public function set_template(string $template) {
        $this->template = $template;
    }

    /**
     * Get template name
     * 
     * @return string
     */
    protected function get_template() {
        if (empty($this->template)) {
            $template = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $this->get_base_class_name()));

			return $template;
        }

        return $this->template;
    }


    /**
     * set admin script
     * @param bool $is_admin_script
     * @return void
     */
    public function set_is_admin_script(bool $is_admin_script) {
        $this->is_admin_script = $is_admin_script;
    }

    /**
     * Get plugin's layout base path
     *
     * @return string
     */
    protected function get_plugin_layout_base_path(): string
    {
        return GROWFUND_WORKING_DIRECTORY . '/resources/views';
    }

    /**
     * Get active theme's layout base path
     *
     * @return string
     */
    protected function get_theme_layout_base_path(): string
    {
        return get_stylesheet_directory() . '/growfund';
    }

    /**
     * Get the template path
     */
    public function get_template_path() {
        $plugin_layout_base_path = $this->get_plugin_layout_base_path();
        $theme_layout_base_path = $this->get_theme_layout_base_path();

        $template_dir = $this->get_template_dir();
        $template = $this->get_template();
        
        $path = !empty($template_dir) ? $template_dir . '/' . $template . '.php' : $template . '.php';

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
     * Render a view
     * @return void
     */
    public function render() {
        return static::echo_safe_html($this->get_html());
    }

    /**
     * Get HTML as string of a view page and load scripts and styles
     * @return string
     */
    public function get_html() {
        $template_path = $this->get_template_path();

        $variable_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $this->get_base_class_name()));

        extract([$variable_name => $this->get_values()]); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        $output = '';

        ob_start();
        include $template_path;
        $output = ob_get_clean();

        return $output;
    }

    protected function register_assets() {
        $hook_name = $this->is_admin_script ? HookNames::ADMIN_ENQUEUE_SCRIPT : HookNames::WP_ENQUEUE_SCRIPT;

        growfund_app(AssetHandler::class)->register($hook_name, static::class, $this->load_scripts($hook_name));
    }

    /**
     * Get HTML with escaping as string of a view page and load scripts and styles
     * @return string
     */
    public function get_safe_html() {
        return static::safe_html($this->get_html());
    }   

    /**
     * Get SVG icon content
     * @since 1.0.4
     * @param string $svg_icon_path
     * @return string
     */
    public function get_svg_icon($svg_icon_path) {
        if (empty($svg_icon_path)) {
            return '';
        }
        
        $file_path = GROWFUND_RESOURCE_PATH . ltrim($svg_icon_path, '/');

        if (! file_exists($file_path)) {
            return '';
        }

        ob_start();
        include $file_path;
        $output = ob_get_clean();

        return $output;
    }

    protected function load_scripts(string $hook_name) {
        return function () use ($hook_name) {
            if (did_action($hook_name)) {
                $this->enqueue_styles();
				$this->enqueue_scripts();
                return;
            }
			add_action($hook_name, function() {
				$this->enqueue_styles();
				$this->enqueue_scripts();
			}, $this->script_priority);  
        };
    }

    /**
     * enqueue styles
     * @return void
     */
    protected function enqueue_styles() {}

    /**
     * enqueue scripts
     * @return void
     */
    protected function enqueue_scripts() {}

    /**
     * Get allowed html tags
     * 
     * @return array
     */
    public static function get_allowed_html_tags() {
		$allowed_tags = wp_kses_allowed_html( 'post' );

        $form_common_attributes = [
			'id'            => true,
			'class'         => true,
			'name'          => true,
			'value'         => true,
			'title'         => true,
			'style'         => true,
			'disabled'      => true,
			'readonly'      => true,
			'required'      => true,
			'hidden'        => true,
			'tabindex'      => true,
			'accesskey'     => true,
			'autocomplete' => true,
			'autofocus'     => true,
			'form'          => true,
			'aria-label'    => true,
			'aria-hidden'   => true,
			'aria-describedby' => true,
			'data-*'        => true,
            'xml:lang'      => true,
		];

        $input_attributes = array_merge($form_common_attributes, [
			'type'         => true,
			'placeholder'  => true,
			'checked'      => true,
			'maxlength'    => true,
			'minlength'    => true,
			'min'          => true,
			'max'          => true,
			'step'         => true,
			'pattern'      => true,
			'size'         => true,
			'multiple'     => true,
			'accept'       => true,
			'src'          => true,
			'alt'          => true,
			'list'         => true,
		]);

        $select_attributes = array_merge($form_common_attributes, [
			'multiple' => true,
			'size'     => true,
		]);

        $option_attributes = [
			'value'    => true,
			'selected' => true,
			'disabled' => true,
			'label'    => true,
            'data-*'   => true,
            'xml:lang' => true,
		];

        $form_tags = [
            'form'     => [
                'action'    => true,
                'method'    => true,
                'class'     => true,
                'id'        => true,
                'data-*'   => true,
				'xml:lang' => true,
            ],
			'input'    => $input_attributes,
			'select'   => $select_attributes,
			'option'   => $option_attributes,
			'label'    => [
				'for'       => true,
				'class'     => true,
				'id'        => true,
				'style'     => true,
                'data-*'    => true,
                'xml:lang'      => true,
			],
			'fieldset' => [
				'disabled' => true,
				'form'     => true,
				'name'     => true,
				'class'    => true,
				'id'       => true,
                'data-*'   => true,
                'xml:lang'      => true,
			],
			'legend' => [
				'class'  => true,
				'id'     => true,
                'data-*' => true,
                'xml:lang'      => true,
			],
		];

        $svg_allowed_tags = [
			'svg',
			'g',
			'path',
			'circle',
			'rect',
			'line',
			'ellipse', 
			'polygon',
			'polyline',
			'text',
			'tspan',
			'defs', 
			'linearGradient',
			'radialGradient',
			'stop',
			'desc',
			'use',
			'mask'
		];

        $svg_common_attributes = [
			'id'             => true,
			'class'          => true,
			'style'          => true,
			'fill'           => true,
			'fill-opacity'   => true,
			'fill-rule'      => true,
			'stroke'         => true,
			'stroke-width'   => true,
			'stroke-linecap' => true,
			'stroke-linejoin'=> true,
			'stroke-opacity' => true,
			'd'              => true,
			'x'              => true,
			'y'              => true,
			'width'          => true,
			'height'         => true,
			'viewBox'        => true,
            'viewbox'        => true,
			'xmlns'          => true,
			'transform'      => true,
			'mask'           => true,
			'maskUnits'      => true,
			'maskunits'      => true,
			'x1'             => true,
			'y1'             => true,
			'x2'             => true,
			'y2'             => true,
			'cx'             => true,
			'cy'             => true,
			'r'              => true,
			'rx'             => true,
			'ry'             => true,
			'points'         => true,
			'offset'         => true,
			'stop-color'     => true,
			'stop-opacity'   => true,
			'xlink:href'     => true,
		];

		$svg_tags = array_fill_keys($svg_allowed_tags, $svg_common_attributes);

        $extra_tags = [
            'iframe' => [
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'frameborder'     => true,
				'allow'           => true,
				'allowfullscreen' => true,
				'loading'         => true,
				'title'           => true,
				'name'            => true,
				'id'              => true,
				'class'           => true,
				'style'           => true,
				'sandbox'         => true,
				'referrerpolicy'  => true,
				'scrolling'       => true,
                'importance'      => true,
                'data-*'          => true,
                'xml:lang'        => true,
			],
            'a' => [
                'disabled' => true,
                'href' => true,
                'target' => true,
                'class' => true,
                'id' => true,
                'data-*' => true,
                'xml:lang' => true,
            ],
            'style' => [
                'type' => true,
            ],
        ];

        $video_tags = [
            'source' => [
                'src' => true,
                'type' => true,
                'data-*' => true,
                'xml:lang' => true,
            ]
        ];

        $allowed_tags = array_merge($allowed_tags, $svg_tags, $form_tags, $video_tags, $extra_tags);

        return $allowed_tags;
    }

    /**
     * return a string containing HTML by escaping any disallowed
     * @param string $html The HTML to render.
     * @return string
     */
    public static function safe_html($html) {
        return wp_kses($html, static::get_allowed_html_tags());
    }

	/**
	 * Safely render a string containing HTML by escaping any disallowed
	 * tags.
	 * @param string $html The HTML to render.
     * 
     * @return void
	 */
    public static function echo_safe_html($html) {
        echo wp_kses($html, static::get_allowed_html_tags());
    }
}
