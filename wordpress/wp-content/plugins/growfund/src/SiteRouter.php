<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Middleware;
use Growfund\Exceptions\InvalidMiddlewareException;
use Growfund\Http\SiteRequest;
use Exception;
use Growfund\Constants\HookNames;
use Growfund\Core\AssetHandler;
use Growfund\Exceptions\InvalidRoutActionException;

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

    /**
     * Array of class instances.
     *
     * @since 1.1.0
     * @var array
     */
    protected static $instances = [];


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

        if (growfund_is_block_theme()) {
			add_action('init', function() {
                register_block_type('growfund/site-page', [
                    'render_callback' => [static::class, 'resolve'],
                ]);

                if (growfund_is_react_site()) {
                    register_block_type('growfund/react-header', [
						'render_callback' => function () {
                            return block_template_part('header');
                        },
					]);

                    register_block_type('growfund/react-footer', [
						'render_callback' => function () {
                            return block_template_part('footer');
                        },
					]);
                }
			});
        } else {
			add_action('template_redirect', [static::class, 'resolve']);
        }
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
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
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

        $request_method = wp_unslash(growfund_input_server('REQUEST_METHOD', ''));
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

        $request_method = wp_unslash(growfund_input_server('REQUEST_METHOD', ''));
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

        $request_method = wp_unslash(growfund_input_server('REQUEST_METHOD', ''));
        $route = static::find_route($request_method, $query);

        if (empty($route)) {
            wp_die('Route not found.');
        }

        if (!class_exists($route->action[0]) || !method_exists($route->action[0], $route->action[1])) {
            wp_die(esc_html__('Controller or method not found.', 'growfund'), 404);
        }

		if ($route->needs_nonce_check) {
            $request_nonce = wp_unslash(growfund_input_post('_wpnonce') ?? growfund_input_get('_wpnonce') ?? '');
			$nonce_action = $route->nonce_action ?? growfund_with_prefix('site_nonce');

			if (!wp_verify_nonce($request_nonce, $nonce_action) ) {
				wp_die(esc_html__('Security check failed.', 'growfund'), 403);
			}
		}

        $controller_name = $route->action[0];
        
        if (!class_exists($controller_name)) {
            /* translators: %s: Controller class */
            throw new InvalidRoutActionException(sprintf(esc_html__('Controller %s not found', 'growfund'), esc_html($controller_name)));
        }

        $controller = static::make($controller_name);
        $method_name = $route->action[1];

        if (!method_exists($controller, $method_name)) {
            /* translators: 1: Method name, 2: Controller class */
            throw new InvalidRoutActionException(sprintf(esc_html__('The method %1$s is missing in the controller %2$s', 'growfund'), esc_html($method_name), esc_html($controller_name)));
        }

        $request = static::get_request_instance(
            $request_nonce ?? null, 
            $nonce_action ?? null
        );

        $params = static::extract_params($route->pattern);

        $request->set_attributes($params);
        $params = array_values($params);

        array_unshift($params, $request);

        $request_method = wp_unslash(growfund_input_server('REQUEST_METHOD', ''));

        try {
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
                    growfund_app(AssetHandler::class)->load_assets();

                    if (growfund_is_block_theme()) {
                        return wp_kses($result, View::get_allowed_html_tags());
                    }

                    $is_react_view = growfund_is_react_site();

                    growfund_get_header($is_react_view);

                    add_filter('the_content', function () use ($result) {                        
                        return wp_kses($result, View::get_allowed_html_tags());
                    }, 20);

                    growfund_get_footer($is_react_view);

                    return;
                }

                exit;
            }
        } catch (Exception $e) {
            return SiteExceptionHandler::handle($e);
        }

        wp_die(esc_html__('Method not allowed.', 'growfund'));
    }

    /**
     * Cache a class instance.
     *
     * @since 1.1.0
     *
     * @param string $abstract The class name to bind
     * @param object $instance The instance of the class
     * @return void
     */
    protected static function cache(string $abstract, $instance)
    {
        static::$instances[$abstract] = $instance;
    }

    /**
     * Check if a class instance is cached.
     *
     * @since 1.1.0
     *
     * @param string $abstract The class name to check
     * @return bool
     */
    protected static function is_cached(string $abstract)
    {
        return isset(static::$instances[$abstract]);
    }

    /**
     * Get a cached class instance.
     *
     * @since 1.1.0
     *
     * @param string $abstract The class name to get
     * @return object
     */
    protected static function get_cached(string $abstract)
    {
        return static::$instances[$abstract];
    }

    /**
     * Resolve a class and its dependencies.
     *
     * @param string $abstract The class name to resolve
     * @param array $resolving Stack of classes being resolved (for 
     * circular dependency detection)
     *
     * @return object The resolved instance
     * @throws Exception When class doesn't exist, has circular dependencies, or other resolution errors
     *
     * @example
     * $instance = static::make(MyClass::class);
     */
    public static function make(string $abstract, array $resolving = [])
    {
        if (static::is_cached($abstract)) {
            return static::get_cached($abstract);
        }

        if (in_array($abstract, $resolving, true)) {
            /* translators: %s: Class name */
            throw new Exception(sprintf(esc_html__('Circular dependency detected for class "%s".', 'growfund'), esc_html($abstract)));
        }

        if (!class_exists($abstract)) {
            /* translators: %s: Class name */
            throw new Exception(sprintf(esc_html__('Class "%s" does not exist.', 'growfund'), esc_html($abstract)));
        }

        $reflector = new \ReflectionClass($abstract);

        if ($reflector->isAbstract()) {
            /* translators: %s: Class name */
            throw new Exception(sprintf(esc_html__('Class "%s" is abstract and cannot be instantiated.', 'growfund'), esc_html($abstract)));
        }

        $constructor = $reflector->getConstructor();

        if (!$constructor) {
            return new $abstract();
        }

        if (!$constructor->isPublic()) {
            /* translators: %s: Class name */
            throw new Exception(sprintf(esc_html__('Class "%s" has a non-public constructor and cannot be instantiated.', 'growfund'), esc_html($abstract)));
        }

        $dependencies = [];
        $resolving[] = $abstract;

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if (!$type) {
                /* translators: %s: Parameter name */
                throw new Exception(sprintf(esc_html__('Parameter "%s" is missing a type hint in the constructor. Please add a class type hint.', 'growfund'), esc_html($parameter->getName())));
            }

            if ($type->isBuiltin()) {
                /* translators: %s: Parameter name */
                throw new Exception(sprintf(esc_html__('Parameter "%s" must be a class type, not a built-in type. Please specify a valid class dependency.', 'growfund'), esc_html($parameter->getName())));
            }

            $dependencies[] = static::is_cached($type->getName())
                ? static::get_cached($type->getName())
                : static::make($type->getName(), $resolving);
        }

        $instance = $reflector->newInstanceArgs($dependencies);
        static::cache($abstract, $instance);

        return $instance;
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
        $url = trim(wp_parse_url(growfund_input_server('REQUEST_URI', ''), PHP_URL_PATH), '/');
        $url_parts = explode('/', $url);
        $pattern_parts = explode('/', trim($pattern, '/'));

        $params = [];

        foreach ($pattern_parts as $i => $part) {
            if (preg_match('#^{(.+)}$#', $part)) {
                $param_key = trim($part, '{}');
                $params[$param_key] = isset($url_parts[$i]) ? $url_parts[$i] : null;
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
    protected static function get_request_instance($request_nonce, $nonce_action)
    {
        return SiteRequest::instance($request_nonce, $nonce_action);
    }
}
