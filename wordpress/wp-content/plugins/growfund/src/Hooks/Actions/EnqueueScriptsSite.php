<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
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
        if (growfund_is_react_site()) {
            return;
        }

        $script_url = GROWFUND_DIR_URL . 'resources/assets/site/scripts/common.js';
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
            'has_growfund_pro' => growfund_has_growfund_pro(),
            'currency_info' => [
                'currency' => growfund_settings(AppSettings::PAYMENT)->get_currency(),
				'currency_position' => growfund_settings(AppSettings::PAYMENT)->get_currency_position(),
				'decimal_places' => growfund_settings(AppSettings::PAYMENT)->get_decimal_places(),
				'decimal_separator' => growfund_settings(AppSettings::PAYMENT)->get_decimal_separator(),
				'thousand_separator' => growfund_settings(AppSettings::PAYMENT)->get_thousand_separator(),
            ]
        ]);

        wp_register_script('growfund-inline-script', false, ['growfund-core'], GROWFUND_VERSION, true);
        wp_enqueue_script('growfund-inline-script');

        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/common.css';

        wp_enqueue_style('growfund-main-styles', $main_styles_url, [], GROWFUND_VERSION);

        $font_url = GROWFUND_DIR_URL . 'resources/assets/css/growfund-font.css';

        wp_enqueue_style('growfund-inter-font', $font_url, [], GROWFUND_VERSION);
        wp_style_add_data('growfund-inter-font', 'preload', 'style');

        $rich_text_editor_styles_url = GROWFUND_DIR_URL . 'resources/assets/css/rich-text-editor.css';

        if (growfund_is_valid_file($rich_text_editor_styles_url)) {
            wp_enqueue_style(
                'growfund-rich-text-editor-styles',
                $rich_text_editor_styles_url,
                ['growfund-main-styles'],
                GROWFUND_VERSION
            );
        }

        $this->load_woocommerce_assets();
    }

    protected function load_woocommerce_assets()
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
}
