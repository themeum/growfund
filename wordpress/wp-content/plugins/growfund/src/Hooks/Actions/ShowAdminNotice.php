<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;

class ShowAdminNotice extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_NOTICES;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (Option::get('permalink_structure') === '') {
            $permalink_url = esc_url(admin_url('options-permalink.php'));
            $permalink_text = __('Settings → Permalinks', 'growfund');

            $message = sprintf(
                /* translators: %s: link to the Permalink Settings page */
                __('⚠️ Your site is using Plain permalinks. Growfund plugin routes require Pretty Permalinks. Please update your permalink settings here: %s.', 'growfund'),
                '<a href="' . $permalink_url . '">' . $permalink_text . '</a>'
            );

            growfund_echo_safe_html(sprintf('<div class="notice notice-error is-dismissible"><p>%s</p></div>', $message));
        }

        if (!growfund_is_plugin_menu() && growfund_app()->is_migration_available_from_crowdfunding()) {
            ?>
            <div id="growfund_admin_migration_banner" class="growfund-admin-migration-banner notice">
                <img
                    src="<?php echo esc_url(growfund_image_url('/images/migration-top-banner.webp')); ?>"
                    alt="Migration Banner"
                    class="growfund-admin-migration-banner__image"
                />

                <div class="growfund-admin-migration-banner__content">
                    <div class="growfund-admin-migration-banner__text">
                        <h3 class="growfund-admin-migration-banner__title">
                            <?php esc_html_e('Migrate to Growfund', 'growfund'); ?>
                        </h3>
                        <p class="growfund-admin-migration-banner__subtitle">
                            <?php esc_html_e('Move to Growfund for the best crowdfunding experience in WordPress.', 'growfund'); ?>
                        </p>
                    </div>

                    <div class="growfund-admin-migration-banner__actions">
                        <a href="<?php echo esc_url(growfund_admin_url('migrate-from-crowdfunding')); ?>" class="growfund-admin-migration-banner__button">
                            <span class="dashicons dashicons-randomize"></span>
                            <?php esc_html_e('Migrate Now', 'growfund'); ?>
                        </a>
                        <button class="growfund-admin-migration-banner__button--close">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}
