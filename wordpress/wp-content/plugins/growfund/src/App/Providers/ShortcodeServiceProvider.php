<?php

namespace Growfund\App\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\ServiceProvider;
use Growfund\SiteExceptionHandler;
use Exception;
use Growfund\View;

class ShortcodeServiceProvider extends ServiceProvider
{
    /**
     * Register the shortcodes to the application.
     *
     * @return void
     */
    public function register()
    {
        $shortcodes  = require GROWFUND_DIR_PATH . '/configs/shortcodes.php';
        $this->app->tag($shortcodes, 'shortcodes');
    }

    /**
     * The shortcodes will be added on after the application booted.
     *
     * @return void
     */
    public function boot()
    {
        $shortcodes = $this->app->tagged('shortcodes');

        foreach ($shortcodes as $shortcode) {
            add_shortcode($shortcode->get_name(), function () use ($shortcode) {
                try {
                    if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
                        return '<div class="growfund-shortcode-placeholder">' . esc_html__('Shortcode Preview', 'growfund') . '</div>';
                    }
                    
                    return wp_kses($shortcode->resolve(...func_get_args()), View::get_allowed_html_tags());
                } catch (Exception $error) {
                    return SiteExceptionHandler::handle($error);
                }
            });
        }
    }
}
