<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Application;
use Growfund\Constants\AppConfigKeys;
use Growfund\Supports\Utils;

if (!defined('ABSPATH')) {
    exit;
}

require_once GROWFUND_DIR_PATH . '/routes/api.php';
require_once GROWFUND_DIR_PATH . '/routes/site.php';
require_once GROWFUND_DIR_PATH . '/routes/ajax.php';

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

$growfund_app = Application::configure();
$growfund_app->boot();

return $growfund_app;
