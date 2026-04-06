<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Application;
use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Supports\Option;
use Growfund\Supports\Utils;

if (!defined('ABSPATH')) {
    exit;
}

require_once GROWFUND_DIR_PATH . 'routes/api.php';
require_once GROWFUND_DIR_PATH . 'routes/site.php';
require_once GROWFUND_DIR_PATH . 'routes/ajax.php';

/**
 * Register a macro to the application.
 * 
 * @return boolean
 */
Application::macro('is_donation_mode', function () {
    return Utils::is_donation_mode();
});

/**
 * Register a macro to decide if the woocommerce plugin is installed or not
 * 
 * @return bool
 */
Application::macro('is_woocommerce_installed', function () {
    return boolval(intval(is_plugin_active('woocommerce/woocommerce.php') ?? 0));
});

/**
 * Register a macro to decide if the onboarding is completed or not
 * 
 * @return bool
 */
Application::macro('is_onboarding_completed', function () {
    return boolval(intval(get_option(AppConfigKeys::IS_ONBOARDING_COMPLETED) ?? 0));
});

/**
 * Register a macro to check if the migration is available from crowdfunding.
 * 
 * @return boolean
 */
Application::macro('is_migration_available_from_crowdfunding', function () {
    return Utils::is_migration_available_from_crowdfunding();
});

/**
 * Register a macro to get the installed database version.
 * 
 * @return string
 */
Application::macro('installed_db_version', function () {
    return Option::get(OptionKeys::INSTALLED_DB_VERSION, '0.0.0');
});

/**
 * Register a macro to get if the pro plugin is activated.
 * 
 * @return string
 */
Application::macro('has_growfund_pro', function () {
    if (!function_exists('is_plugin_active')) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    return is_plugin_active('growfund-pro/growfund-pro.php');
});

$growfund_app = Application::configure();
$growfund_app->boot();

return $growfund_app;
