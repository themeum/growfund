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
class EnqueueScriptsAdmin extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_ENQUEUE_SCRIPT;
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
        if (!growfund_is_plugin_menu()) {
            wp_enqueue_style('growfund-global-style-extended', GROWFUND_DIR_URL . 'resources/assets/css/global-style-extended.css', [], GROWFUND_VERSION);
            
            $global_script = GROWFUND_DIR_URL . 'resources/assets/js/growfund-global-script.js';
			wp_enqueue_script(
                'growfund-global-js',
                $global_script,
                [],
                GROWFUND_VERSION,
                true
			);
        
            return;
        }

        wp_enqueue_style(
            'growfund-inter-font',
            GROWFUND_DIR_URL . 'resources/assets/css/growfund-font.css',
            [],
            false // phpcs:ignore
        );

        $dashboard_script = GROWFUND_DIR_URL . 'resources/assets/dashboard/scripts/dashboard.js';

        wp_enqueue_script(
            'growfund-dashboard-js',
            $dashboard_script,
            [],
            GROWFUND_VERSION,
            true
        );

        wp_enqueue_script('wp-tinymce');
        wp_enqueue_editor();

        wp_enqueue_media();
        // phpcs:ignore -- intentionally ignored enqueue version
        wp_enqueue_style('growfund-admin-style-extended', GROWFUND_DIR_URL . 'resources/assets/css/admin-style-extended.css');

        if (!growfund_is_dev_mode()) {
            Assets::enqueue_vite_assets();
        }
    }
}
