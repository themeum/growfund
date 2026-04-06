<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Application;
use Growfund\Contracts\Capability;
use Growfund\Core\AppSettings;
use Growfund\Core\Dispatcher;
use Growfund\Core\FeatureManager;
use Growfund\Core\Scheduler;
use Growfund\Core\User;
use Growfund\Http\Response;
use Growfund\Mailer;
use Growfund\Http\SiteResponse;
use Growfund\PostTypes\Campaign;
use Growfund\Renderer;
use Growfund\Sanitizer;
use Growfund\Supports\Auth;
use Growfund\SiteRouter;
use Growfund\Supports\Assets;
use Growfund\Supports\Currency;
use Growfund\Supports\DataCaster;
use Growfund\Supports\FlashMessage;
use Growfund\Supports\Location;
use Growfund\Supports\Url;
use Growfund\Supports\Payment;
use Growfund\Supports\RequestInput;
use Growfund\Supports\Utils;
use Growfund\Supports\Woocommerce;
use Growfund\View;

if (!function_exists('growfund_app')) {
    /**
     * Get the container instance.
     *
     * @template TClass
     *
     * @param string|class-string<TClass>|null $name
     * @param array $parameters
     *
     * @return ($name is null ? \Growfund\Application : TClass)
     */
    function growfund_app($name = null, array $parameters = [])
    {
        $app = Application::get_instance();

        if (!is_null($name)) {
            return $app->make($name, $parameters);
        }

        return $app;
    }
}

if (!function_exists('growfund_renderer')) {
    /**
     * Get the renderer instance for rendering the view templates.
     *
     * @return \Growfund\Renderer
     */
    function growfund_renderer()
    {
        return growfund_app()->make(Renderer::class);
    }
}

if (!function_exists('growfund_render')) {
    /**
     * Render the view class.
     * @since 1.0.3
     * 
     * @param View $view_class
     * @return void
     */
    function growfund_render(View $view_class)
    {
        $view_class->render();
    }
}

if (!function_exists('growfund_get_html')) {
    /**
     * Get the html for the view class.
     * @since 1.0.3
     * 
     * @param View $view_class
     * @return string
     */
    function growfund_get_html(View $view_class)
    {        
        return $view_class->get_html();
    }
}


if (!function_exists('growfund_echo_safe_html')) {
    /**
     * Render a view with allowed tags
     * @param string $content
     * @return void
     */
    function growfund_echo_safe_html($content) {
        return View::echo_safe_html($content);
    }
}

if (!function_exists('growfund_get_safe_html')) {
    /**
     * Render a view with allowed tags
     * @param View|string $content
     * @return string
     */
    function growfund_get_safe_html($content) {
        if (is_subclass_of($content, View::class)) {
            return $content->get_safe_html();
        }

        return View::safe_html($content);
    }
}

if (!function_exists('growfund_user')) {
    /**
     * Get the user instance.
     *
     * @return Growfund\Core\User
     */
    function growfund_user($user_id = null)
    {
        return growfund_app()->make(User::class, ['user_id' => $user_id]);
    }
}

if (!function_exists('growfund_settings')) {
    /**
     * Get the settings instance.
     *
     * @template T of 'general'|'pages'|'campaigns'|'notifications'|'payment'|'permissions'|'receipts'|'security'|'advanced'|'branding'
     * @param T $key
     * @return (T is 'branding' ? \Growfund\App\Settings\BrandingSettings : (T is 'campaigns' ? \Growfund\App\Settings\CampaignSettings : (T is 'notifications' ? \Growfund\App\Settings\EmailAndNotificationSettings : (T is 'pages' ? \Growfund\App\Settings\PageSettings : (T is 'payment' ? \Growfund\App\Settings\PaymentSettings : (T is 'permissions' ? \Growfund\App\Settings\PermissionSettings : (T is 'receipts' ? \Growfund\App\Settings\ReceiptSettings : (T is 'security' ? \Growfund\App\Settings\SecuritySettings : (T is 'advanced' ? \Growfund\App\Settings\AdvancedSettings : \Growfund\App\Settings\GeneralSettings)))))))))
     */
    function growfund_settings($key)
    {        
        return growfund_app()->make($key);
    }
}


if (!function_exists('growfund_dd')) {
    /**
     * Dump and die
     * 
     * @param mixed ...$args
     * @return never
     */
    function growfund_dd(...$args)
    {
        echo '<xmp>';
        foreach ($args as $arg) {
            echo "\n";
            var_dump($arg); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
            echo "\n";
        }
        echo '</xmp>';
        die();
    }
}

if (!function_exists('growfund_pr')) {
    /**
     * print and die
     * 
     * @param mixed ...$args
     * @return never
     */
    function growfund_pr(...$args)
    {
        echo '<xmp>';
        foreach ($args as $arg) {
            echo "\n";
            print_r($arg); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
            echo "\n";
        }
        echo '</xmp>';
        die();
    }
}

if (!function_exists('growfund_response')) {
    /**
     * Get the response instance.
     *
     * @return \Growfund\Http\Response
     */
    function growfund_response()
    {
        return Response::create()->with_headers([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'no-referrer-when-downgrade',
            'Cache-Control' => 'public, max-age=60, stale-while-revalidate=30',
        ]);
    }
}

if (!function_exists('growfund_site_response')) {
    /**
     * Get the SiteResponse instance for handling Site related JSON responses.
     *
     * @return \Growfund\Http\SiteResponse
     */
    function growfund_site_response()
    {
        return new SiteResponse();
    }
}

if (!function_exists('growfund_with_prefix')) {
    /**
     * Get the key with prefix applied.
     *
     * @param string $key
     * @return string
     */
    function growfund_with_prefix(string $key)
    {
        return GROWFUND_PREFIX . preg_replace('/^' . GROWFUND_PREFIX . '/', '', $key);
    }
}

if (!function_exists('growfund_without_prefix')) {
    /**
     * Get the key without prefix applied.
     *
     * @param string $key
     * @return string
     */
    function growfund_without_prefix(string $key)
    {
        return preg_replace('/^' . GROWFUND_PREFIX . '/', '', $key) ?? $key;
    }
}

if (!function_exists('growfund_cast_value')) {
    /**
     * Cast the value according to the data type
     *
     * @param mixed $value
     * @param string|null $type
     * @return mixed
     */
    function growfund_cast_value($value, $type = null)
    {
        return DataCaster::cast_value($value, $type);
    }
}

if (!function_exists('growfund_cast_data')) {
    /**
     * Cast the data according to the data type mapping
     *
     * @param array|object $data
     * @param array $map
     * @return array
     * @throws Exception
     */
    function growfund_cast_data($data, array $map)
    {
        return DataCaster::cast_data($data, $map);
    }
}


if (!function_exists('growfund_is_dev_mode')) {
    /**
     * Check if the development mode is enabled.
     *
     * @return bool
     */
    function growfund_is_dev_mode()
    {
        return defined('GROWFUND_ENV_MODE') && GROWFUND_ENV_MODE === 'development';
    }
}

if (!function_exists('growfund_redirect')) {
    /**
     * Redirect to the given location.
     */
    function growfund_redirect($location, $data = [], $status = 302)
    {
        Url::redirect($location, $data, $status);
    }
}

if (!function_exists('growfund_dispatcher')) {
    /**
     * Get the dispatcher instance.
     * 
     * @return \Growfund\Core\Dispatcher
     */
    function growfund_dispatcher()
    {
        return growfund_app()->make(Dispatcher::class);
    }
}

if (!function_exists('growfund_event')) {
    /**
     * Get the event instance.
     *
     * @param object $event
     * 
     * @return void
     */
    function growfund_event($event)
    {
        growfund_dispatcher()->dispatch($event);
    }
}


if (!function_exists('growfund_is_valid_json')) {
    /**
     * Check if the string is a valid JSON.
     * 
     * @param string $string_value
     * @return bool
     */
    function growfund_is_valid_json($string_value)
    {
        if (!is_string($string_value)) {
            return false;
        }

        json_decode($string_value);

        return (json_last_error() === JSON_ERROR_NONE);
    }
}

if (!function_exists('growfund_flash_message')) {
    /**
     * Get the flash message.
     * 
     * @param string $key
     * @param string|null $message
     * @return string|void
     */
    function growfund_flash_message($key, $message = null)
    {
        if (!empty($message)) {
            FlashMessage::set($key, $message);
        } else {
            return FlashMessage::get($key);
        }
    }
}

if (!function_exists('growfund_flash_set_message')) {
    /**
     * Set the flash message.
     * 
     * @param string $key
     * @param string|array $message
     * @return void
     */
    function growfund_flash_set_message($key, $message)
    {
        FlashMessage::set($key, $message);
    }
}

if (!function_exists('growfund_flash_get_message')) {
    /**
     * Get the flash message.
     * 
     * @param string $key
     * @return string
     */
    function growfund_flash_get_message($key)
    {
        return FlashMessage::get($key);
    }
}

if (!function_exists('growfund_email')) {
    /**
     * Get the mailer instance.
     * 
     * @param string $mailer The class name of the mailer.
     *
     * @return \Growfund\Mailer
     */
    function growfund_email(string $mailer)
    {
        if (!class_exists($mailer) || !is_subclass_of($mailer, Mailer::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    /* translators: %s: Mailer class name. */
                    esc_html__('The mailer class must be a subclass of %s', 'growfund'),
                    Mailer::class
                )
            );
        }

        return growfund_app()->make($mailer);
    }
}

if (!function_exists('growfund_get_all_capabilities')) {
    /**
     * Get all the capabilities.
     * 
     * @param string|null $role
     * @return array
     */
    function growfund_get_all_capabilities($role = null)
    {
        $capabilities = require GROWFUND_DIR_PATH . 'configs/capabilities.php';

        $applicable_capabilities = [];

        foreach ($capabilities as $capability) {
            if (!class_exists($capability)) {
                /* translators: %s: Capability class name */
                throw new Exception(sprintf(esc_html__('Class "%s" does not exist.', 'growfund'), esc_html($capability)));
            }

            $instance = new $capability();

            if (!$instance instanceof Capability) {
                throw new Exception(esc_html__('Class must implement \Growfund\Contracts\Capability.', 'growfund'));
            }

            $applicable_capabilities = array_merge($applicable_capabilities, $instance->get_capabilities($role) ?? []);
        }

        return $applicable_capabilities;
    }
}

if (!function_exists('growfund_payment_engine')) {
    function growfund_payment_engine()
    {
        return Payment::get_engine();
    }
}

if (!function_exists('growfund_wc_product_id')) {
    function growfund_wc_product_id()
    {
        return Woocommerce::get_growfund_product_id();
    }
}

if (!function_exists('growfund_wc_product_slug')) {
    function growfund_wc_product_slug()
    {
        return Woocommerce::get_growfund_product_slug();
    }
}

/**
 * Make a scheduler instance.
 * 
 * @return \Growfund\Core\Scheduler
 */
if (!function_exists('growfund_scheduler')) {
    /**
     * Make a scheduler instance.
     * 
     * @return \Growfund\Core\Scheduler
     */
    function growfund_scheduler()
    {
        return Scheduler::make();
    }
}

if (!function_exists('growfund_placeholder_image_url')) {
    /**
     * Get the placeholder image URL.
     *
     * @return string
     */
    function growfund_placeholder_image_url()
    {
        return Assets::get_assets_url() . '/images/placeholder.webp';
    }
}

if (!function_exists('growfund_image_url')) {
    /**
     * Get the image URL.
     *
     * @return string
     */
    function growfund_image_url(string $path)
    {
        return Assets::get_assets_url() . $path;
    }
}


if (!function_exists('growfund_admin_url')) {
    /**
     * Get the image URL.
     *
     * @return string
     */
    function growfund_admin_url(string $path)
    {
        return admin_url('admin.php?page=growfund#/' . $path);
    }
}


if (!function_exists('growfund_campaign_url')) {

    /**
     * Get the campaign URL by identifier or post ID.
     * If the identifier is numeric, it is treated as a post ID.
     * If the identifier is empty, the current page's URL is returned.
     * If the identifier is a string, it is treated as a campaign slug.
     * 
     * @param string|number $identifier The campaign identifier (post ID or slug).
     * @return string The campaign URL or false if the campaign does not exist.
     */
    function growfund_campaign_url($identifier = null)
    {
        if (empty($identifier)) {
            return get_permalink();
        }

        if (is_numeric($identifier)) {
            return get_permalink((int) $identifier);
        }

        $post = get_page_by_path($identifier, OBJECT, Campaign::NAME);

        return $post ? get_permalink($post->ID) : null;
    }
}

if (!function_exists('growfund_campaign_archive_url')) {
	/**
	 * Get the URL of the campaign archive page.
	 *
	 * @return string The URL of the campaign archive page.
	 */
    function growfund_campaign_archive_url()
    {
        $page_id = growfund_settings(AppSettings::PAGES)->get_campaigns_page_id();
        
        return !empty($page_id) ? get_permalink($page_id) : get_post_type_archive_link(Campaign::NAME);
    }
}

if (!function_exists('growfund_to_currency')) {
    /**
     * Alias function to format an amount to currency formatted string.
     *
     * @param float|int $amount
     * @return string
     */
    function growfund_to_currency($amount)
    {
        return Currency::format($amount);
    }
}

if (!function_exists('growfund_clean_path')) {
    /**
     * Clean and normalize file paths for consistency.
     *
     * @param string $path
     * @param bool   $trailing_slash Add a trailing slash? Default true.
     * @return string
     */
    function growfund_clean_path(string $path, bool $trailing_slash = true)
    {
        $path = wp_normalize_path($path);
        return $trailing_slash ? trailingslashit($path) : untrailingslashit($path);
    }
}

if (!function_exists('growfund_payment_gateway')) {
    /**
     * Get the instance of the payment method
     *
     * @param string $name
     * @param bool $throwable
     * @return \Growfund\Payments\Contracts\PaymentGatewayContract|\Growfund\Payments\Contracts\FuturePaymentContract|\Growfund\Payments\Contracts\PaymentConfigurationContract|null
     */
    function growfund_payment_gateway(string $name, bool $throwable = true)
    {
        if ($throwable) {
            return growfund_app()->make($name);
        }

        try {
            return growfund_app()->make($name);
        } catch (Throwable $_) {
            return null;
        }
    }
}


if (!function_exists('growfund_uuid')) {
    /**
     * @return string
     */
    function growfund_uuid()
    {
        return Utils::uuid();
    }
}

if (!function_exists('growfund_pledge_receipt_download_url')) {
    /**
     * Get the pledge receipt download URL
     * @param string $uid
     * @return string
     */
    function growfund_pledge_receipt_download_url(string $uid)
    {
        return Utils::pledge_receipt_url($uid);
    }
}

if (!function_exists('growfund_donation_receipt_download_url')) {
    /**
     * Get the donation receipt download URL
     * @param string $uid
     * @return string
     */
    function growfund_donation_receipt_download_url(string $uid)
    {
        return Utils::donation_receipt_url($uid);
    }
}

if (!function_exists('growfund_get_all_campaign_ids_by_fundraiser')) {
    function growfund_get_all_campaign_ids_by_fundraiser()
    {
        return growfund_app()->make('fundraiser_campaign_ids');
    }
}

if (!function_exists('growfund_backer_dashboard_url')) {
    /**
     * Get the backer dashboard URL
     * @return string
     */
    function growfund_backer_dashboard_url()
    {
        return site_url("/dashboard/backer");
    }
}

if (!function_exists('growfund_donor_dashboard_url')) {
    /**
     * Get the donor dashboard URL
     * @return string
     */
    function growfund_donor_dashboard_url()
    {
        return site_url("/dashboard/donor");
    }
}

if (!function_exists('growfund_fundraiser_dashboard_url')) {
    /**
     * Get the fundraiser dashboard URL
     * @return string
     */
    function growfund_fundraiser_dashboard_url()
    {
        return site_url("/dashboard/fundraiser");
    }
}

if (!function_exists('growfund_user_dashboard_url')) {
    /**
     * Get the user dashboard URL based on role
     * 
     * @param int|null $user_id
     * @return string
     */
    function growfund_user_dashboard_url($user_id = null)
    {
        if (growfund_user($user_id)->is_admin()) {
            return admin_url('admin.php?page=growfund');
        }

        if (growfund_user($user_id)->is_fundraiser()) {
            return growfund_fundraiser_dashboard_url();
        }

        if (growfund_user($user_id)->is_backer()) {
            return growfund_backer_dashboard_url();
        }

        if (growfund_user($user_id)->is_donor()) {
            return growfund_donor_dashboard_url();
        }

        return site_url();
    }
}

if (!function_exists('growfund_url')) {
    /**
     * Make a URL with query vars
     * 
     * @param string $url
     * @param array $query_vars
     * @return string
     */
    function growfund_url($url, $query_vars = [])
    {
        return Url::make($url, $query_vars);
    }
}

if (!function_exists('growfund_login_url')) {
    /**
     * Get the login URL
     * 
     * @param string $redirect_to
     * 
     * @return string
     */
    function growfund_login_url($redirect_to = '')
    {
        return Auth::login_url($redirect_to);
    }
}

if (!function_exists('growfund_register_url')) {
    /**
     * Get the register URL
     * 
     * @param bool $is_fundraiser
     * 
     * @return string
     */
    function growfund_register_url($is_fundraiser = false)
    {
        return Auth::register_url($is_fundraiser);
    }
}

if (!function_exists('growfund_forget_password_url')) {
    /**
     * Get the forget password URL
     * 
     * @return string
     */
    function growfund_forget_password_url()
    {
        return Auth::forget_password_url();
    }
}

if (!function_exists('growfund_reset_password_url')) {
    /**
     * Get the reset password URL
     * 
     * @return string
     */
    function growfund_reset_password_url()
    {
        return Auth::reset_password_url();
    }
}

if (!function_exists('growfund_ecard_download_url')) {
    /**
     * Get the donation receipt download URL
     * @param string $uid
     * @return string
     */
    function growfund_ecard_download_url(string $uid)
    {
        return site_url("public/#donations/$uid/ecard");
    }
}

if (!function_exists('growfund_is_valid_file')) {
    /**
     * Check if the file is valid
     * @param string $url
     * @return bool
     */
    function growfund_is_valid_file($url)
    {
        $file_path = str_replace(GROWFUND_DIR_URL, GROWFUND_DIR_PATH ?? '', $url);
        $normalized_path = wp_normalize_path($file_path);
        $plugin_dir = wp_normalize_path(GROWFUND_DIR_PATH);

        if (strpos($normalized_path, $plugin_dir) !== 0) {
            return false;
        }

        return file_exists($normalized_path) && is_readable($normalized_path);
    }
}

if (!function_exists('growfund_is_auth_page')) {
    /**
     * Check if the current page is an authentication page
     * @return bool
     */
    function growfund_is_auth_page()
    {
        $current_route = SiteRouter::get_current_route_name();

        if (empty($current_route)) {
            return false;
        }

        return strpos($current_route, 'auth.') === 0;
    }
}

if (!function_exists('growfund_is_checkout_page')) {
    /**
     * Check if the current page is a checkout page
     * @return bool
     */
    function growfund_is_checkout_page()
    {
        return Utils::is_checkout_page();
    }
}

if (!function_exists('growfund_is_donation_checkout_page')) {
    /**
     * Check if the current page is a donation checkout page
     * @return bool
     */
    function growfund_is_donation_checkout_page()
    {
        return Utils::is_donation_checkout_page();
    }
}

if (!function_exists('growfund_is_pledge_checkout_page')) {
    /**
     * Check if the current page is a pledge checkout page
     * @return bool
     */
    function growfund_is_pledge_checkout_page()
    {
        return Utils::is_pledge_checkout_page();
    }
}

if (!function_exists('growfund_remote_request_base_url')) {
    /**
     * Get the Growfund site's request base url.
     * This is the base url for getting the payment plugins
     * And other resources from the Growfund site.
     *
     * @return string
     */
    function growfund_remote_request_base_url()
    {
        return growfund_is_dev_mode()
            ? getenv('GROWFUND_REMOTE_REQUEST_BASE_URL')
            : 'https://growfund.com/api';
    }
}

if (!function_exists('growfund_remote_request_url')) {
    /**
     * Get the Growfund site's request url.
     * This is the url for getting the payment plugins
     * And other resources from the Growfund site.
     *
     * @param string $path
     * @return string
     */
    function growfund_remote_request_url($path)
    {
        $path = str_starts_with($path, '/') ? $path : sprintf('/%s', $path);

        return wp_normalize_path(
            sprintf('%s%s', growfund_remote_request_base_url(), $path)
        );
    }
}


if (!function_exists('growfund_terms_and_conditions_url')) {
    function growfund_terms_and_conditions_url()
    {
        $page_id = (int) growfund_settings(AppSettings::PAGES)->get_terms_and_conditions_page_id();

        return !empty($page_id) ? get_permalink($page_id) : site_url();
    }
}

if (!function_exists('growfund_privacy_policy_url')) {
    function growfund_privacy_policy_url()
    {
        $page_id = (int) growfund_settings(AppSettings::PAGES)->get_privacy_policy_page_id();
        
        return !empty($page_id) ? get_permalink($page_id) : site_url();
    }
}

if (!function_exists('growfund_social_sharing_options')) {
    function growfund_social_sharing_options()
    {
        return growfund_settings(AppSettings::CAMPAIGNS)->social_shares();
    }
}

if (!function_exists('growfund_site_assets_url')) {
    /**
     * Get the site assets URL.
     * @param string|null $path
     * @return string
     */
    function growfund_site_assets_url($path = null)
    {
        $base_url = GROWFUND_DIR_URL . 'resources/assets/site/';

        return $path ? $base_url . $path : $base_url;
    }
}

if (!function_exists('growfund_site_image_url')) {
    /**
     * Get the site image URL.
     * @param string $image
     * @return string
     */
    function growfund_site_image_url($image)
    {
        $base_url = GROWFUND_DIR_URL . 'resources/assets/site/images/';

        return $base_url . $image;
    }
}

if (!function_exists('growfund_site_placeholder_image_url')) {
    /**
     * Get the placeholder image URL for site.
     * @param bool $is_square
     * @return string
     */
    function growfund_site_placeholder_image_url($is_square = true)
    {
        return growfund_site_image_url($is_square ? 'placeholder-square.webp' : 'placeholder-rectangle.webp');
    }
}

if (!function_exists('growfund_user_avatar')) {
    /**
     * Get the placeholder image URL for site.
     *
     * @return string
     */
    function growfund_user_avatar()
    {
        return growfund_site_image_url('avatar.webp');
    }
}

if (!function_exists('growfund_get_header')) {
    /**
     * Get the header
     *
     * @return void
     */
    function growfund_get_header($only_assets = false)
    {
        if (growfund_is_block_theme() && !$only_assets) {
            return block_template_part('header');
        }

        if ($only_assets) : ?>
            <!doctype html>
            <html <?php language_attributes(); ?>>

            <head>
                <meta charset="<?php bloginfo('charset'); ?>">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <?php
                wp_head();
                ?>
            </head>

            <body <?php body_class(); ?>>
                <?php wp_body_open(); ?>
            <?php
            else :
				get_header();
        endif;
    }
}

if (!function_exists('growfund_get_footer')) {
    /**
     * Get the footer
     *
     * @return void
     */
    function growfund_get_footer($only_assets = false)
    {
        if (growfund_is_block_theme() && !$only_assets) {
            return block_template_part('footer');
        }

        if ($only_assets) :
			wp_footer();
			?>
            </body>

            </html>
			<?php
        else :
            get_footer();
        endif;
    }
}

if (!function_exists('growfund_pretty_location')) {
    /**
     * Return a pretty version of the given location
     *
     * @param string $location The location, in the format "country:state"
     * @return string The pretty version of the location
     */
    function growfund_pretty_location(string $location)
    {
        return Location::get_pretty_location($location);
    }
}

if (!function_exists('growfund_is_block_theme')) {
    /**
     * Check if the site is using a block template
     *
     * This function will return true if the site is using a block template and false otherwise.
     *
     * @return bool True if the site is using a block template, false otherwise.
     */
    function growfund_is_block_theme()
    {
        return function_exists('wp_is_block_theme') && wp_is_block_theme();
    }
}

if (!function_exists('growfund_app_features')) {
    /**
     * @deprecated since 1.0.2
     * keep this function for backward compatibility. will be removed in version 1.1.0
     *
     * @return FeatureManager
     */
    function growfund_app_features()
    {
        return growfund_app()->make(FeatureManager::class);
	}
}

if (!function_exists('growfund_is_dashboard_route')) {
    /**
     * Check if the current route is a dashboard route.
     *
     * This function will return true if the current route is a dashboard route and false otherwise.
     *
     * @return bool True if the current route is a dashboard route, false otherwise.
     */
    function growfund_is_dashboard_route()
    {
        return Utils::is_dashboard_route();
    }
}

if (!function_exists('growfund_is_public_route')) {
    /**
     * Check if the current route is a public route.
     *
     * This function will return true if the current route is a public route and false otherwise.
     *
     * @return bool True if the current route is a public route, false otherwise.
     */
    function growfund_is_public_route()
    {
        return Utils::is_public_route();
    }
}

if (!function_exists('growfund_is_react_site')) {
    /**
     * Check if the current route is running as a React site.
     *
     * This function will return true if the current route is running as a React site and false otherwise.
     *
     * @return bool
     */
    function growfund_is_react_site()
    {
        return Utils::is_react_site();
    }
}

if (!function_exists('growfund_is_plugin_menu')) {
    /**
     * Check if the current route is a plugin menu page.
     *
     * This function will return true if the current route is a plugin menu route and false otherwise.
     *
     * @return bool True if the current route is a plugin menu route, false otherwise.
     */
    function growfund_is_plugin_menu()
    {
        if (growfund_input_get('page') === 'growfund') {
            return true;
        }

        return false;
    }
}

if (!function_exists('growfund_nonce_field')) {
    /**
     * Echo a nonce field for the given action.
     *
     * If no action is given, it will default to the site nonce.
     *
     * @param string $action The action to generate the nonce for.
     */
    function growfund_nonce_field($action = null)
    {
        $action = empty($action) ? growfund_with_prefix('site_nonce') : $action;
        
        return wp_nonce_field($action);
    }
}

if (!function_exists('growfund_is_wc_checkout')) {
    /**
     * Check if the current route is a WooCommerce checkout page.
     *
     * This function will return true if the current route is a WooCommerce checkout page and false otherwise.
     *
     * @return bool True if the current route is a WooCommerce checkout page, false otherwise.
     */
    function growfund_is_wc_checkout()
    {
        return Utils::is_woocommerce_checkout_page();
    }
}

if (!function_exists('growfund_is_support_future_payment')) {
	/**
	 * Check if the given payment method supports future payments.
	 *
	 * This function will return true if the payment method supports future payments and false otherwise.
	 *
	 * @param string $payment_method The name of the payment method.
	 * @return bool True if the payment method supports future payments, false otherwise.
	 */
    function growfund_is_support_future_payment($payment_method)
    {
        return Payment::is_support_future_payment($payment_method);
    }
}

if (!function_exists('growfund_logout')) {
    /**
     * Logout the current user from growfund.
     * @return void
     */
    function growfund_logout()
    {
        wp_logout();
    }
}

if (!function_exists('growfund_site_name')) {
    /**
     * Get the name of the site.
     */
    function growfund_site_name() {
        return get_bloginfo('name');
    }
}

if (!function_exists('growfund_input_get')) {
    /**
     * Retrieve a value from the $_GET.
     * 
     * @param string $key The key to retrieve.
     * @param mixed $default The default value to return if the key is not found.
     * @param string $sanitizer_type The type of sanitizer to apply to the value.
     * @return mixed The value of the key, or the default value if not found.
     */
    function growfund_input_get($key, $default = null, $sanitizer_type = Sanitizer::TEXT) {
        $input = new RequestInput('get', $key, $default, $sanitizer_type);

        return $input->get();
    }
}

if (!function_exists('growfund_input_post')) {
    /**
     * Retrieve a value from the $_POST.
     * 
     * @param string $key The key to retrieve.
     * @param mixed $default The default value to return if the key is not found.
     * @param string $sanitizer_type The type of sanitizer to apply to the value.
     * @return mixed The value of the key, or the default value if not found.
     */
    function growfund_input_post($key, $default = null, $sanitizer_type = Sanitizer::TEXT) {
        $input = new RequestInput('post', $key, $default, $sanitizer_type);

        return $input->get();
    }
}

if (!function_exists('growfund_input_server')) {
    /**
     * Retrieve a value from the $_SERVER.
     * 
     * @param string $key The key to retrieve.
     * @param string $sanitizer_type The type of sanitizer to apply to the value.
     * @param mixed $default The default value to return if the key is not found.
     * @return mixed The value of the key, or the default value if not found.
     */
    function growfund_input_server($key, $default = null, $sanitizer_type = Sanitizer::TEXT) {
        $input = new RequestInput('server', $key, $default, $sanitizer_type);

        return $input->get();
    }
}


if (!function_exists('growfund_error_log')) {
    function growfund_error_log(string $message) {
        if (growfund_is_dev_mode() && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($message); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- logging in dev mode
        }
    }
}

if (!function_exists('growfund_is_valid_url')) {
    function growfund_is_valid_url($url) {
        if (!preg_match('/^https?:\/\//', $url)) {
			return false;
		}

		if (!filter_var($url, FILTER_VALIDATE_URL)) {
			return false;
		}

		return true;
    }
}

if (!function_exists('growfund_file_put_contents')) {
    /**
     * Write content to a file.
     * 
     * @param string $filename The path to the file.
     * @param string $content The content to write to the file.
     * @param string $mode The mode to open the file in. Defaults to 'write'. options: 'write', 'append', 'prepend'
     * @return bool
     */
    function growfund_file_put_contents(string $filename, string $content, string $mode = 'write') {
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();

        global $wp_filesystem;

		if (!$wp_filesystem) {
			return false;
		}

        if ($mode === 'append') {
            $content = $wp_filesystem->get_contents($filename) . $content;
        } elseif ($mode === 'prepend') {
            $content = $content . $wp_filesystem->get_contents($filename);
        }

        return $wp_filesystem->put_contents($filename, $content, FS_CHMOD_FILE);
    }
}