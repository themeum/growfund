<?php
/**
* Plugin Name:       Growfund
* Plugin URI:        https://growfund.com
* Description:       Launch your donation or reward-based WordPress crowdfunding platform with Growfund. It combines native payments, WooCommerce integration, real-time insights, and fully customizable campaigns, making it the ultimate WordPress crowdfunding plugin.
* Version:           1.0.1
* Author:            Themeum
* Author URI:        https://themeum.com
* Text Domain:       growfund
* Requires PHP:      7.4
* Requires at least: 5.9
* Tested up to:      6.8
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

/**
 * Define plugin version
 */
define('GROWFUND_VERSION', '1.0.1');

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
define('GROWFUND_REACT_APP_URL', GROWFUND_DIR_URL . 'resources/ts/');

/**
 * Define the react application root path
 * @since 1.0.0
 */
define('GROWFUND_REACT_APP_PATH', GROWFUND_DIR_PATH . 'resources/ts/');

/**
 * Define plugin base name
 * @since   1.0.0
 */
define('GROWFUND_BASENAME', plugin_basename(__FILE__));

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

		add_action('after_plugin_row_' . GROWFUND_BASENAME, function () {
			echo '
                <script type="text/javascript">
                    document.querySelector("tr[data-slug=\'growfund\']").classList.add("update");
                </script>
                <tr class="plugin-update-tr active">
                    <td colspan="4" class="plugin-update colspanchange">
                        <div class="notice inline notice-warning notice-alt">
                            <p><strong>Growfund:</strong> You must deactivate <strong>Growfund Pro</strong> before deactivating Growfund.</p>
                        </div>
                    </td>
                </tr>
            ';
		});
	}
    

    require_once __DIR__ . '/bootstrap/app.php';
}

require_once __DIR__ . '/helpers.php';
