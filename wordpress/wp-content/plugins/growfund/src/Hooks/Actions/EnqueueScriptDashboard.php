<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Assets;

/**
 * This hook is responsible for enqueueing the admin script.
 *
 * @since 1.0.0
 */
class EnqueueScriptDashboard extends BaseHook
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
        if (!growfund_is_react_site()) {
            return;
        }

        if (growfund_is_dev_mode()) {
            Assets::load_vite_client();
        }

        $dashboard_script = GROWFUND_DIR_URL . 'resources/assets/dashboard/scripts/dashboard.js';

        wp_enqueue_script(
            'growfund-dashboard-js',
            $dashboard_script,
            [],
            GROWFUND_VERSION,
            true
        );

        wp_enqueue_style(
            'growfund-inter-font',
            GROWFUND_DIR_URL . 'resources/assets/css/growfund-font.css',
            [],
            false // phpcs:ignore
        );

        wp_enqueue_script('wp-tinymce');
        wp_enqueue_editor();

        wp_enqueue_media();
        
        // phpcs:ignore -- intentionally ignored enqueue version
        wp_enqueue_style('growfund-admin-style-extended', GROWFUND_DIR_URL . 'resources/assets/css/admin-style-extended.css');

        if (!growfund_is_dev_mode()) {
            Assets::enqueue_vite_assets();
        }

        remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
        remove_action('wp_footer', 'wp_enqueue_global_styles', 1);

        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }
}
