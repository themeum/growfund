<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\PostTypes\Campaign;
use Growfund\Supports\Utils;
use Growfund\Supports\Woocommerce;

class EnqueueScriptsSite extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_ENQUEUE_SCRIPT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function get_priority()
    {
        return 1;
    }

    public function handle(...$args)
    {
        if (growfund_is_dashboard_route()) {
            return;
        }

        $this->enqueue_core_assets();
        $this->enqueue_campaign_assets();
        $this->enqueue_auth_assets();
        $this->enqueue_checkout_assets();
        $this->woocommerce_assets();
    }

    protected function enqueue_core_assets()
    {
        $this->enqueue_core_scripts();

        if (!growfund_is_checkout_page() && !$this->should_load_auth_assets() && !$this->should_load_campaign_assets()) {
            return;
        }

        $this->enqueue_font();
        $this->enqueue_core_styles();
        $this->enqueue_component_styles($this->get_component_styles_list());
        $this->enqueue_component_scripts($this->get_component_scripts_list());
    }

    protected function enqueue_font()
    {
        $font_url = GROWFUND_DIR_URL . 'resources/assets/css/growfund-font.css';
        $handle = 'growfund-inter-font';

        wp_enqueue_style($handle, $font_url, [], GROWFUND_VERSION);
        wp_style_add_data($handle, 'preload', 'style');
    }


    protected function enqueue_core_styles()
    {
        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/styles.css';

        if (growfund_is_valid_file($main_styles_url)) {
            wp_enqueue_style(
                'growfund-main-styles',
                $main_styles_url,
                ['growfund-inter-font'],
                GROWFUND_VERSION
            );
        }

        $rich_text_editor_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/rich-text-editor.css';
        if (growfund_is_valid_file($rich_text_editor_styles_url)) {
            wp_enqueue_style(
                'growfund-rich-text-editor-styles',
                $rich_text_editor_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
        }
    }

    protected function enqueue_core_scripts()
    {
        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/script.js';
        wp_register_script(
            'growfund-core',
            $script_url,
            [],
            GROWFUND_VERSION,
            true
        );
        wp_enqueue_script('growfund-core');

        wp_localize_script('growfund-core', 'growfund', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'rest_url' => esc_url_raw(rest_url()),
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'ajax_nonce' => wp_create_nonce(growfund_with_prefix('ajax_nonce')),
            'site_url' => esc_url(site_url()),
            'login_url' => esc_url(growfund_login_url()),
            'forget_password_url' => esc_url(growfund_forget_password_url()),
            'checkout_url' => esc_url(Utils::get_checkout_url()),
            'is_dev_mode' => growfund_is_dev_mode(),
            'has_growfund_pro' => has_growfund_pro(),
            'currency_info' => [
                'currency' => growfund_settings(AppSettings::PAYMENT)->get_currency(),
				'currency_position' => growfund_settings(AppSettings::PAYMENT)->get_currency_position(),
				'decimal_places' => growfund_settings(AppSettings::PAYMENT)->get_decimal_places(),
				'decimal_separator' => growfund_settings(AppSettings::PAYMENT)->get_decimal_separator(),
				'thousand_separator' => growfund_settings(AppSettings::PAYMENT)->get_thousand_separator(),
            ]
        ]);
    }

    protected function woocommerce_assets()
    {
        if (function_exists('is_checkout') && is_checkout() && Woocommerce::is_native_checkout()) {
            wp_enqueue_script(
                'growfund-woocommerce-classic-checkout',
                GROWFUND_DIR_URL . 'resources/assets/site/scripts/woocommerce/classic-checkout.js',
                ['jquery', 'growfund-core'],
                GROWFUND_VERSION,
                true
            );
        }
    }

    protected function enqueue_campaign_assets()
    {
        if (!$this->should_load_campaign_assets()) {
            return;
        }

        $this->enqueue_component_styles($this->get_campaign_component_styles_list());
        $this->enqueue_component_scripts($this->get_campaign_component_scripts_list());

        wp_register_script('growfund-inline-script', false, ['growfund-core'], GROWFUND_VERSION, true);
        wp_enqueue_script('growfund-inline-script');


        $media_slider_style_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/media-slider.css';
        if (growfund_is_valid_file($media_slider_style_url)) {
            wp_enqueue_style(
                'growfund-media-slider-styles',
                $media_slider_style_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
        }

        $media_slider_script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/media-slider.js';
        if (growfund_is_valid_file($media_slider_script_url)) {
            wp_enqueue_script(
                'growfund-media-slider-js',
                $media_slider_script_url,
                ['growfund-core'],
                GROWFUND_VERSION,
                true
            );
        }
    }

    protected function should_load_campaign_assets()
    {
        return is_post_type_archive(Campaign::NAME)
            || is_singular(Campaign::NAME)
            || Utils::has_campaign_shortcode();
    }
    
    protected function should_load_auth_assets()
    {
        return growfund_is_auth_page() || Utils::has_auth_shortcode();
    }

    protected function enqueue_auth_assets()
    {
        if (!$this->should_load_auth_assets()) {
            return;
        }

        $base_style_path = GROWFUND_DIR_URL . 'resources/assets/site/styles/auth/';
        $base_script_path = GROWFUND_DIR_URL . 'resources/assets/site/scripts/auth/';

        wp_enqueue_style('growfund-login-css', $base_style_path . 'login.css', [], GROWFUND_VERSION);
        wp_enqueue_style('growfund-register-css', $base_style_path . 'register.css', [], GROWFUND_VERSION);

        wp_enqueue_script('growfund-login-js', $base_script_path . 'login.js', ['growfund-core'], GROWFUND_VERSION, true);
        wp_enqueue_script('growfund-register-js', $base_script_path . 'register.js', ['growfund-core'], GROWFUND_VERSION, true);
    }

    protected function enqueue_checkout_assets()
    {
        if (!growfund_is_checkout_page()) {
            return;
        }

        $base_style_path = GROWFUND_DIR_URL . 'resources/assets/site/styles/checkout/';
        $base_script_path = GROWFUND_DIR_URL . 'resources/assets/site/scripts/checkout/';

        wp_enqueue_style('growfund-checkout-css', $base_style_path . 'checkout.css', [], GROWFUND_VERSION);
        wp_enqueue_style('growfund-donation-checkout-css', $base_style_path . 'donation-checkout.css', [], GROWFUND_VERSION);

        wp_enqueue_script('growfund-checkout-js', $base_script_path . 'checkout.js', ['growfund-core'], GROWFUND_VERSION, true);
        wp_enqueue_script('growfund-donation-checkout-js', $base_script_path . 'donation-checkout.js', ['growfund-core'], GROWFUND_VERSION, true);
    }

    protected function enqueue_component_styles(array $component_styles)
    {
        $base_path = GROWFUND_DIR_URL . 'resources/assets/site/styles/components/';

        foreach ($component_styles as $style) {
            $css_url = $base_path . $style . '.css';
            $handle = 'growfund-' . $style . '-css';

            if (growfund_is_valid_file($css_url)) {
                wp_enqueue_style($handle, $css_url, [], GROWFUND_VERSION);
            }
        }
    }

    protected function enqueue_component_scripts(array $component_scripts)
    {
        $base_path = GROWFUND_DIR_URL . 'resources/assets/site/scripts/';

        foreach ($component_scripts as $script) {
            $js_url = $base_path . $script . '.js';
            $handle = sprintf('growfund-%s-js', $script);

            if (!growfund_is_valid_file($js_url) || !$this->is_script_needed($script)) {
                continue;
            }

            $dependencies = $this->get_script_dependencies($script);
            wp_enqueue_script($handle, $js_url, $dependencies, GROWFUND_VERSION, true);
        }
    }

    protected function get_script_dependencies($script)
    {
        $dependencies = ['growfund-core'];

        if (in_array($script, ['updates-infinite-scroll', 'rewards-infinite-scroll'], true)) {
            $dependencies[] = 'growfund-infinite-scroll-js';
        }

        if ($script === 'updates-infinite-scroll') {
            $dependencies[] = 'growfund-timeline-updates-js';
        }

        if ($script === 'social-share') {
            $dependencies[] = 'growfund-updates-js';
        }

        return $dependencies;
    }

    protected function is_script_needed($script)
    {
        $page_specific_scripts = [
            'campaigns-archive' => is_post_type_archive(Campaign::NAME),
            'infinite-scroll' => is_post_type_archive(Campaign::NAME) || is_singular(Campaign::NAME),
            'updates-infinite-scroll' => is_singular(Campaign::NAME),
            'rewards-infinite-scroll' => is_singular(Campaign::NAME),
            'comments' => is_singular(Campaign::NAME),
            'mobile-filters' => is_post_type_archive(Campaign::NAME) || Utils::has_campaign_shortcode(),
            'filters' => is_post_type_archive(Campaign::NAME) || Utils::has_campaign_shortcode(),
            'search' => is_post_type_archive(Campaign::NAME) || Utils::has_campaign_shortcode(),
            'pledge-modal' => is_singular(Campaign::NAME),
            'donation-modal' => is_singular(Campaign::NAME),
            'donation-item' => is_singular(Campaign::NAME),
        ];

        return $page_specific_scripts[$script] ?? true;
    }

    protected function get_campaign_component_styles_list()
    {
        return [
            'campaign-card',
            'campaign-list',
            'campaign-sidebar',
            'campaign-slider',
            'comment',
            'faq',
            'filters',
            'mobile-filters',
            'pledge-modal',
            'recommendations',
            'tab-content-campaign',
            'tab-content-comments',
            'tab-content-faq',
            'tab-content-rewards',
            'tab-content-updates',
            'tabs',
            'update-item',
            'video',
            'empty-state',
            'donation-list',
            'donation-modal',
            'donation-item',
            'countdown',
            'support-section',
            'modal',
            'contribution-success-modal',
            'contribution-failed-modal',
        ];
    }
    protected function get_component_styles_list()
    {
        return [
            'header',
            'badge',
            'button',
            'dropdown',
            'expandable-text',
            'form-builder',
            'icon',
            'input',
            'search-input',
            'payment-method-selector',
            'textarea',
            'campaign-reward',
            'order-summary',

            'forgot-password',
            'reset-password',
        ];
    }

    protected function get_campaign_component_scripts_list()
    {
        return [
            'tabs',
            'updates',
            'faq',
            'timeline-updates',
            'campaign-slider',
            'mobile-filters',
            'filters',
            'search',
            'campaigns-archive',
            'infinite-scroll',
            'updates-infinite-scroll',
            'rewards-infinite-scroll',
            'comments',
            'pledge-modal',
            'donation-modal',
            'video',
            'countdown',
            'social-share',
            'bookmark',
            'campaign-card-bookmark',
            'support-section',
            'modal',
            'expandable-collaborators'
        ];
    }

    protected function get_component_scripts_list()
    {
        return [
            'dropdown',
            'payment-method-selector',
            'order-summary',
            'password-toggle',
            'expandable-text',

            'forgot-password',
            'reset-password',

            'icon',
        ];
    }
}
