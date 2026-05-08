<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\CurrencyConfig;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Utils;

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
            'site_nonce' => wp_create_nonce(growfund_with_prefix('site_nonce')),
            'site_url' => esc_url(site_url()),
            'login_url' => esc_url(growfund_login_url()),
            'forget_password_url' => esc_url(growfund_forget_password_url()),
            'checkout_url' => esc_url(Utils::get_checkout_url()),
            'is_dev_mode' => growfund_is_dev_mode(),
            'has_growfund_pro' => growfund_app()->has_growfund_pro(),
            'currency_info' => growfund_app()->make(CurrencyConfig::class)->get()
        ]);

        $main_styles_url = GROWFUND_DIR_URL . 'resources/assets/site/styles/common.css';

        wp_enqueue_style('growfund-main-styles', $main_styles_url, [], GROWFUND_VERSION);

        $rich_text_editor_styles_url = GROWFUND_RESOURCE_URL . 'assets/site/styles/rich-text-editor.css';

        wp_enqueue_style(
            'growfund-rich-text-editor-styles',
            $rich_text_editor_styles_url,
            ['growfund-main-styles'],
            GROWFUND_VERSION
        );

        $this->load_woocommerce_assets();
    }

    protected function load_woocommerce_assets()
    {
        if (growfund_is_wc_checkout()) {
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
