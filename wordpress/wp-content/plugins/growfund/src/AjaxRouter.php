<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Http\SiteRequest;
use Exception;
use Growfund\Exceptions\InvalidRoutActionException;

class AjaxRouter
{
    /**
     * All registered ajax routes/actions.
     *
     * @var array
     */
    protected static $routes = [];

    /**
     * Ajax action.
     *
     * @var string
     */
    protected $action;

    /**
     * Controller and method to call.
     *
     * @var array
     */
    protected $callback;

    /**
     * User type for the ajax request
     *
     * @var string|null
     */
    protected $user_type = null;

    /**
     * Whether the route requires nonce check.
     *
     * @var bool
     */
    protected $needs_nonce_check = false;

    /**
     * The nonce action/name for verification.
     *
     * @var string|null
     */
    protected $nonce_action = null;

    /**
     * Bootstrap the ajax router.
     *
     * @return void
     */
    public static function register()
    {
        foreach (static::$routes as $route) {
            if (count($route->callback) < 2) {
                throw new Exception(esc_html__('Invalid ajax callback handler', 'growfund'));
            }

            $controller_name = $route->callback[0];

            if (!class_exists($controller_name)) {
                /* translators: %s: Controller name */
                throw new Exception(sprintf(esc_html__('Controller %s not found', 'growfund'), esc_html($controller_name)));
            }

            $controller = SiteRouter::make($controller_name);
            $method_name = $route->callback[1];

            if (!method_exists($controller, $method_name)) {
                /* translators: 1: Method name, 2: Controller class */
                throw new InvalidRoutActionException(sprintf(esc_html__('The method %1$s is missing in the controller %2$s', 'growfund'), esc_html($method_name), esc_html($controller_name)));
            }

            $callback = function () use ($controller, $method_name, $route) {
                // Perform nonce verification if required
                if ($route->needs_nonce_check) {
                    $request_nonce = wp_unslash(growfund_input_post('_wpnonce') ?? growfund_input_get('_wpnonce') ?? '');
                    $nonce_action = $route->nonce_action ?? growfund_with_prefix('site_nonce');

                    if (!wp_verify_nonce($request_nonce, $nonce_action)) {
                        wp_send_json_error(__('Security check failed', 'growfund'), 403);
                        return;
                    }
                }

                try {
                    $request = static::get_request_instance(
                        $request_nonce ?? null, 
                        $nonce_action ?? null
                    );

                    return $controller->{$method_name}($request);
                } catch (Exception $error) {
                    return static::send_error_response($error);
                }
            };

            if ($route->user_type === 'guest' || $route->user_type === null) {
                add_action('wp_ajax_nopriv_' . $route->action, $callback);
            }

            if ($route->user_type === 'auth' || $route->user_type === null) {
                add_action('wp_ajax_' . $route->action, $callback);
            }
        }
    }

    /**
     * Add an ajax route/action.
     */
    public static function add_action($action, $callback)
    {
        $route = new static();
        $route->action = $action;
        $route->callback = $callback;

        static::$routes[] = $route;

        return $route;
    }

    /**
     * Add a guest route
     */
    public function for_guest()
    {
        $this->user_type = 'guest';

        return $this;
    }

    /**
     * Set the route to require nonce check.
     */
    public function with_nonce($nonce_action = null)
    {
        $this->needs_nonce_check = true;
        $this->nonce_action = $nonce_action ?? growfund_with_prefix('site_nonce');

        return $this;
    }

    /**
     * Add an authenticated route
     */
    public function for_authenticated()
    {
        $this->user_type = 'auth';

        return $this;
    }

    /**
     * Get the SiteRequest instance.
     */
    protected static function get_request_instance($request_nonce, $nonce_action)
    {
        return SiteRequest::instance($request_nonce, $nonce_action);
    }

    /**
     * Send error response
     *
     * @param Exception $error
     * @return void
     */
    protected static function send_error_response(Exception $error)
    {
        // @todo: Need to unify the error response structure
        if ($error instanceof ValidationException) {
            return growfund_site_response()->json_error([
                'message' => $error->getMessage(),
                'errors' => $error->get_errors(),
            ], $error->getCode() ? $error->getCode() : 422);
        }

        return growfund_site_response()->json_error([
            'message' => $error->getMessage(),
            'code' => $error->getCode(),
        ], Response::INTERNAL_SERVER_ERROR);
    }
}
