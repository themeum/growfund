<?php
/**
* Plugin Name:       Growfund – Ultimate Donation & Crowdfunding Solution
* Plugin URI:        https://growfund.com
* Description:       Launch your donation or reward-based WordPress crowdfunding platform with Growfund. It combines native payments, WooCommerce integration, real-time insights, and fully customizable campaigns, making it the ultimate WordPress crowdfunding plugin.
* Version:           1.0.7
* Author:            Themeum
* Author URI:        https://themeum.com
* Text Domain:       growfund
* Requires PHP:      7.4
* Requires at least: 5.9
* Tested up to:      6.9
* License:           GPLv2 or later
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Domain Path:       /languages
*
* @package Growfund
*/

if (!defined('ABSPATH')) {
    exit;
}

use Growfund\Application;
use Growfund\Constants\HookNames;
use Growfund\Migrations\AddEmailColumnInDonationTable;
use Growfund\Migrations\AddEmailColumnInPledgeTable;

/**
 * Define plugin version
 */
define('GROWFUND_VERSION', '1.0.7');

/**
 * Define plugin file
 * @since   1.0.0
 */
define('GROWFUND_PLUGIN_FILE', __FILE__);

/**
 * Define plugin working directory
 * @since   1.0.0
 */
define('GROWFUND_WORKING_DIRECTORY', dirname(GROWFUND_PLUGIN_FILE));

/**
 * Define plugin root url
 * @since   1.0.0
 */
define('GROWFUND_ROOT_URL', plugin_dir_url(__FILE__));

/**
 * Define plugin directory url
 * @since   1.0.0
 */
define('GROWFUND_DIR_URL', plugin_dir_url(__FILE__));

/**
 * Define plugin directory path
 * @since   1.0.0
 */
define('GROWFUND_DIR_PATH', plugin_dir_path(__FILE__));

/**
 * Define plugin directory src path
 * @since   1.0.0
 */
define('GROWFUND_SRC_PATH', GROWFUND_DIR_PATH . 'src/');

/**
 * Define the react application root url
 * @since 1.0.0
 */
define('GROWFUND_RESOURCE_URL', GROWFUND_DIR_URL . 'resources/');

/**
 * Define the react application root path
 * @since 1.0.0
 */
define('GROWFUND_RESOURCE_PATH', GROWFUND_DIR_PATH . 'resources/');

/**
 * Define plugin base name
 * @since   1.0.0
 */
define('GROWFUND_BASENAME', plugin_basename(__FILE__));

/**
 * Define plugin slug
 * @since   1.0.2
 */
define('GROWFUND_SLUG', dirname(GROWFUND_BASENAME));

/**
 * Define plugin environment mode
 * Available values - development|production
 * @since   1.0.0
 */
define('GROWFUND_ENV_MODE', 'development');

/**
 * Define plugin prefix
 * @since   1.0.0
 */
define('GROWFUND_PREFIX', 'growfund_');

if (!class_exists('ActionScheduler')) {
    require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';
}

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

register_activation_hook(GROWFUND_PLUGIN_FILE, [Application::class, 'handle_activation']);
register_deactivation_hook(GROWFUND_PLUGIN_FILE, [Application::class, 'handle_deactivation']);
register_uninstall_hook(GROWFUND_PLUGIN_FILE, [Application::class, 'handle_uninstall']);

add_action(HookNames::PLUGINS_LOADED, 'growfund_plugin_initializer');

function growfund_plugin_initializer()
{
	if (is_plugin_active('growfund-pro/growfund-pro.php')) {
		add_action('pre_current_active_plugins', function () {
            add_filter('plugin_action_links_' . GROWFUND_BASENAME, function ($actions) {
                if (isset($actions['deactivate'])) {
                    $actions['deactivate'] = '<span style="color:gray;" title="Deactivate Growfund Pro first to disable Growfund.">Deactivate (disabled)</span>';
                }
                return $actions;
            });
		});

        add_filter('plugin_row_meta', function (array $links, string $plugin_name) {
			if ($plugin_name !== GROWFUND_BASENAME) {
				return $links;
			}

			$links[] = '<div class="notice inline notice-warning notice-alt">
                            <p><strong>Growfund:</strong> You must deactivate <strong>Growfund Pro</strong> before deactivating Growfund.</p>
                        </div>';
                        
			return $links;
		}, 10, 2);
	}

    require_once __DIR__ . '/bootstrap/app.php';

    $installed_version = get_option(GROWFUND_PREFIX . 'installed_version');

    if ($installed_version !== GROWFUND_VERSION) {
        (new AddEmailColumnInPledgeTable())->up();
        (new AddEmailColumnInDonationTable())->up();

        update_option(GROWFUND_PREFIX . 'installed_version', GROWFUND_VERSION);
    }
}

require_once __DIR__ . '/helpers.php';
