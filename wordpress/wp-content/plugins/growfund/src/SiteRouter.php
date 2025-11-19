<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Middleware;
use Growfund\Exceptions\InvalidMiddlewareException;
use Growfund\Exceptions\NotFoundException;
use Growfund\Http\SiteRequest;
use Exception;
use Growfund\Constants\HookNames;

/**
 * Custom site router for handling front-end routes using rewrite rules.
 * Supports GET/POST methods, middleware, and pattern-based parameters.
 *
 * @since 1.0.0
 */
class SiteRouter
{
    /**
     * All registered routes.
     *
     * @var array
     */
    protected static $routes = [];

    /**
     * Route name.
     *
     * @var string
     */
    protected $name;

    /**
     * HTTP method (GET, POST).
     *
     * @var string
     */
    protected $method;

    /**
     * The URI pattern (e.g., /funds/{id}).
     *
     * @var string
     */
    protected $pattern;

    /**
     * Controller and method to call.
     *
     * @var array
     */
    protected $action;

    /**
     * Optional middleware class.
     *
     * @var string|null
     */
    protected $middleware;

    /**
     * Param regex constraints (name => regex).
     *
     * @var array
     */
    protected $regex = [];

    /**
     * Whether the route requires a nonce check.
     *
     * @var bool
     */
    protected $needs_nonce_check = false;

    /**
     * Nonce action string to verify.
     *
     * @var string|null
     */
    protected $nonce_action = null;


    public function __construct()
    {
        $this->regex = [];
    }

    /**
     * Bootstrap the router.
     *
     * @return void
     */
    public static function register()
    {
        add_action('init', [static::class, 'register_rewrite_rules']);
        add_filter('query_vars', [static::class, 'add_query_var']);
        add_action('template_redirect', [static::class, 'resolve']);
    }

    /**
     * Register a GET route.
     */
    public static function get($pattern, $action)
    {
        return static::add('GET', $pattern, $action);
    }

    /**
     * Register a POST route.
     */
    public static function post($pattern, $action)
    {
        return static::add('POST', $pattern, $action);
    }

    /**
     * Add a route.
     */
    protected static function add($method, $pattern, $action)
    {
        $route = new static();
        $route->method = strtoupper($method);
        $route->pattern = $pattern;
        $route->action = $action;

        static::$routes[] = $route;

        return $route;
    }

    /**
     * Convert pattern to regex and register the rewrite rule.
     */
    public static function register_rewrite_rules()
    {
        do_action(HookNames::GROWFUND_BEFORE_REGISTER_SITE_ROUTES_ACTION);

        foreach (static::$routes as $route) {
            $regex = $route->get_formatted_pattern_regex();
            $query = 'index.php?growfund_custom_route=' . static::pattern_to_key($route->pattern);
            add_rewrite_rule($regex, $query, 'top');
        }
    }

    /**
     * Add custom query var.
     */
    public static function add_query_var($vars)
    {
        $vars[] = 'growfund_custom_route';
        return $vars;
    }

    /**
     * Get the name of the current route.
     * 
     * @return string|null
     */
    public static function get_current_route_name()
    {
        $query = get_query_var('growfund_custom_route');

        if (empty($query)) {
            return null;
        }

        $request_method = Sanitizer::apply_rule(wp_unslash($_SERVER['REQUEST_METHOD']), Sanitizer::TEXT); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $route = static::find_route($request_method, $query);

        if (empty($route)) {
            return null;
        }

        return $route->name ?? null;
    }


    /**
     * Checks if the current route is a dashboard route.
     * 
     * @return bool
     */
    public static function is_dashboard_route()
    {
        return in_array(static::get_current_route_name(), ['dashboard.fundraiser', 'dashboard.backer', 'dashboard.donor'], true);
    }

    /**
     * Checks if the current request matches a valid custom route.
     *
     * @return bool Whether the current request matches a valid route.
     */
    public static function is_valid_route()
    {
        $query = Sanitizer::apply_rule(get_query_var('growfund_custom_route'), Sanitizer::TEXT);

        if (empty($query)) {
            return false;
        }

        $request_method = Sanitizer::apply_rule(wp_unslash($_SERVER['REQUEST_METHOD']), Sanitizer::TEXT); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $route = static::find_route($request_method, $query);

        if (empty($route)) {
            return false;
        }

        return true;
    }

    /**
     * Match the current request to a route and dispatch it.
     */
    public static function resolve()
    {
        $query = get_query_var('growfund_custom_route');

        if (empty($query)) {
            return;
        }

        $request_method = Sanitizer::apply_rule(wp_unslash($_SERVER['REQUEST_METHOD']), Sanitizer::TEXT); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $route = static::find_route($request_method, $query);

        if (empty($route)) {
            wp_die('Route not found.');
        }

        if (!class_exists($route->action[0]) || !method_exists($route->action[0], $route->action[1])) {
            wp_die(esc_html__('Controller or method not found.', 'growfund'), 404);
        }

		if ($route->needs_nonce_check) {
			$nonce = Sanitizer::apply_rule(wp_unslash($_POST['_wpnonce'] ?? $_GET['_wpnonce'] ?? ''), Sanitizer::TEXT); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$nonce_action = $route->nonce_action ?? growfund_with_prefix('site_nonce');

			if (!wp_verify_nonce($nonce, $nonce_action) ) {
				wp_die(esc_html__('Security check failed.', 'growfund'), 403);
			}
		}


        try {
            $controller = new $route->action[0]; // phpcs:ignore
            $method_name = $route->action[1];
            $request = static::get_request_instance();

            $params = static::extract_params($route->pattern);
            array_unshift($params, $request);

            $request_method = Sanitizer::apply_rule(wp_unslash($_SERVER['REQUEST_METHOD']), Sanitizer::TEXT); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

            if ($request_method === $route->method) {
                $final_callback = function () use ($controller, $method_name, $params) {
                    return call_user_func_array([$controller, $method_name], $params);
                };

                if (!empty($route->middleware)) {
                    $middleware = new $route->middleware();

                    if (!$middleware instanceof Middleware) {
                        throw new InvalidMiddlewareException();
                    }

                    $result = $middleware->handle($request, $final_callback);
                } else {
                    $result = $final_callback();
                }

                if (is_string($result)) {
                    return add_filter('the_content', function ($content) use ($result) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
                        return $result;
                    });
                }

                exit;
            }
        } catch (NotFoundException $e) {
            growfund_redirect(home_url());
        } catch (Exception $e) {
            return SiteExceptionHandler::handle($e);
        }

        wp_die('Method not allowed.');
    }

    /**
     * Convert URI pattern to rewrite rule regex.
     */
    protected function get_formatted_pattern_regex()
    {
        $regex = preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            $param = $matches[1];
            return isset($this->regex[$param]) ? '(' . $this->regex[$param] . ')' : '([^/]+)';
        }, trim($this->pattern, '/'));

        return '^' . $regex . '/?$';
    }

    /**
     * Match current URL to pattern and extract params.
     */
    protected static function extract_params($pattern)
    {
        $url = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'); // phpcs:ignore
        $url_parts = explode('/', $url);
        $pattern_parts = explode('/', trim($pattern, '/'));

        $params = [];

        foreach ($pattern_parts as $i => $part) {
            if (preg_match('#^{(.+)}$#', $part)) {
                $params[] = isset($url_parts[$i]) ? $url_parts[$i] : null;
            }
        }

        return $params;
    }

    /**
     * Convert a pattern to a query-safe key.
     */
    protected static function pattern_to_key($pattern)
    {
        return str_replace('/', '.', $pattern);
    }

    /**
     * Convert a query-safe key back to pattern.
     */
    protected static function key_to_pattern($pattern)
    {
        return str_replace('.', '/', $pattern);
    }

    /**
     * Find a route by method and pattern key.
     */
    protected static function find_route($method, $key)
    {
        $pattern = static::key_to_pattern($key);

        foreach (static::$routes as $route) {
            if (strtolower($route->method) === strtolower($method) && $route->pattern === $pattern) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Attach a name to the route.
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Attach middleware to the route.
     */
    public function middleware($middleware)
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * Require nonce verification for this route.
     */
    public function with_nonce($nonce_action = null)
    {
        $this->needs_nonce_check = true;
        $this->nonce_action = $nonce_action ?? growfund_with_prefix('site_nonce');
        return $this;
    }

    /**
     * Add regex pattern constraints to route parameters.
     */
    public function where($name, $regex)
    {
        $this->regex[$name] = $regex;
        return $this;
    }

    /**
     * Get the SiteRequest instance.
     */
    protected static function get_request_instance()
    {
        return SiteRequest::instance();
    }
}
