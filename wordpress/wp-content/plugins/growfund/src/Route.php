<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use WP_Error;
use Closure;
use Exception;
use Growfund\Exceptions\InvalidRoutActionException;
use Growfund\Http\Request;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Http\Response;

/**
 * Handles route registration and middleware for the growfund REST API.
 *
 * @since 1.0.0
 */
class Route
{
    /**
     * REST API namespace.
     *
     * @since 1.0.0
     * @var string
     */
    protected static $namespace = '';

    /**
     * Array of registered routes.
     *
     * @since 1.0.0
     * @var array
     */
    protected static $routes = [];

    /**
     * Group stack to hold the group options.
     *
     * @since 1.0.0
     * @var array
     */
    protected static $group_stack = [];

    /**
     * HTTP method for the route.
     *
     * @since 1.0.0
     * @var string
     */
    protected $method;

    /**
     * The endpoint path for the route.
     *
     * @since 1.0.0
     * @var string
     */
    protected $endpoint;

    /**
     * Controller class and method for handling the route.
     *
     * @since 1.0.0
     * @var array
     */
    protected $action;

    /**
     * Array of middleware classes.
     *
     * @since 1.0.0
     * @var array
     */
    protected $middleware = [];

    /**
     * Regex patterns.
     *
     * @since 1.0.0
     * @var array
     */
    protected $patterns = [];

    /**
     * Array of class instances.
     *
     * @since 1.0.0
     * @var array
     */
    protected static $instances = [];

    /**
     * Set the API namespace for all registered routes.
     *
     * @since 1.0.0
     *
     * @param string $namespace The namespace for REST API routes.
     * @return void
     */
    public static function set_namespace(string $namespace)
    {
        static::$namespace = $namespace;
    }

    /**
     * Attach middleware to the current route.
     *
     * @since 1.0.0
     *
     * @param string|array $middleware The fully qualified class name of the middleware.
     * @return $this
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);

            return $this;
        }

        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Set a regex pattern for the specific route param.
     *
     * @since 1.0.0
     *
     * @param string $name
     * @param string $regex
     * @return static
     */
    public function where(string $name, string $regex)
    {
        $this->patterns[$name] = $regex;

        return $this;
    }

    /**
     * Get the endpoint in proper format that register_rest_route() expects.
     *
     * @return void
     */
    protected function get_formatted_endpoint()
    {
        return preg_replace_callback('/\{(\w+)\}/', function ($matches) {
            $param = $matches[1];
            $pattern = isset($this->patterns[$param]) ? $this->patterns[$param] : '[^/]+';
            return '(?P<' . $param . '>' . $pattern . ')';
        }, $this->endpoint);
    }

    /**
     * Register a GET route.
     *
     * @since 1.0.0
     *
     * @param string $endpoint The route endpoint.
     * @param array  $action   The controller and method to handle the route.
     * @return static
     */
    public static function get(string $endpoint, array $action)
    {
        $instance = new static();
        $instance->method = 'get';
        $instance->endpoint = $endpoint;
        $instance->action = $action;

        $instance->apply_group_options();

        static::$routes[] = $instance;

        return $instance;
    }

    /**
     * Register a POST route.
     *
     * @since 1.0.0
     *
     * @param string      $endpoint   The route endpoint.
     * @param array       $action     The controller and method to handle the route.
     * @return static
     */
    public static function post(string $endpoint, array $action)
    {
        $instance = new static();
        $instance->method = 'post';
        $instance->endpoint = $endpoint;
        $instance->action = $action;

        $instance->apply_group_options();

        static::$routes[] = $instance;

        return $instance;
    }

    /**
     * Register a PUT route.
     *
     * @since 1.0.0
     *
     * @param string      $endpoint   The route endpoint.
     * @param array       $action     The controller and method to handle the route.
     * @return static
     */
    public static function put(string $endpoint, array $action)
    {
        $instance = new static();
        $instance->method = 'put';
        $instance->endpoint = $endpoint;
        $instance->action = $action;

        $instance->apply_group_options();

        static::$routes[] = $instance;

        return $instance;
    }

    /**
     * Register a PATCH route.
     *
     * @since 1.0.0
     *
     * @param string      $endpoint   The route endpoint.
     * @param array       $action     The controller and method to handle the route.
     * @return static
     */
    public static function patch(string $endpoint, array $action)
    {
        $instance = new static();
        $instance->method = 'patch';
        $instance->endpoint = $endpoint;
        $instance->action = $action;

        $instance->apply_group_options();

        static::$routes[] = $instance;

        return $instance;
    }

    /**
     * Register a DELETE route.
     *
     * @since 1.0.0
     *
     * @param string      $endpoint   The route endpoint.
     * @param array       $action     The controller and method to handle the route.
     * @return static
     */
    public static function delete(string $endpoint, array $action)
    {
        $instance = new static();
        $instance->method = 'delete';
        $instance->endpoint = $endpoint;
        $instance->action = $action;

        $instance->apply_group_options();

        static::$routes[] = $instance;

        return $instance;
    }

    /**
     * Register a group of routes with shared options.
     *
     * This method allows grouping routes under common configuration options 
     * like middleware, or prefix. The closure receives the context 
     * of the group and defines the routes within it.
     *
     * @since 1.0.0
     *
     * @param array   $options  The shared configuration options for the group.
     * @param \Closure $closure The callback that defines the grouped routes.
     *
     * @return void
     */
    public static function group(array $options, Closure $closure)
    {
        static::$group_stack[] = $options;

        $closure();

        array_pop(static::$group_stack);
    }

    /**
     * Get all registered routes.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public static function get_routes()
    {
        return static::$routes;
    }

    /**
     * Apply route group options like prefix and middleware to the route.
     *
     * This method is typically called when a route is defined within a group,
     * applying any shared prefix or middleware from the group stack.
     * 
     * @since 1.0.0
     *
     * @return void
     */
    public function apply_group_options()
    {
        if (!empty(static::$group_stack)) {
            $group = end(static::$group_stack);

            if (!empty($group['prefix'])) {
                $this->endpoint = rtrim($group['prefix'], '/') . '/' . ltrim($this->endpoint, '/');
            }

            if (!empty($group['middleware'])) {
                $this->middleware($group['middleware']);
            }
        }
    }

    /**
     * Register the route with WordPress.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register()
    {
        register_rest_route(static::$namespace, $this->get_formatted_endpoint(), [
            'methods' => strtoupper($this->method),
            'callback' => $this->resolve_route(),
            'permission_callback' => [$this, 'check_permission']
        ]);
    }

    /**
     * Check permissions for the route.
     *
     * @since 1.0.0
     *
     * @param \WP_REST_Request $rest_request
     * @return bool|\WP_Error
     */
    public function check_permission($rest_request)
    {
        $request = Request::from_wp_rest_request($rest_request);

        try {
            $pipeline = array_reduce(
                array_reverse($this->middleware),
                function ($next, $middleware) {
                    return function ($request) use ($next, $middleware) {
                        return (new $middleware())->handle($request, $next);
                    };
                },
                function ($request) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
                    return true;
                }
            );

            return $pipeline($request);
        } catch (AuthorizationException $exception) {
            return new WP_Error('rest_forbidden', $exception->getMessage(), ['status' => Response::FORBIDDEN]);
        } catch (Exception $exception) {
            return new WP_Error('rest_forbidden', $exception->getMessage(), ['status' => Response::INTERNAL_SERVER_ERROR]);
        }
    }

    /**
     * Cache a class instance.
     *
     * @since 1.0.0
     *
     * @param string $abstract The class name to bind
     * @param object $instance The instance of the class
     * @return void
     */
    protected function cache(string $abstract, $instance)
    {
        static::$instances[$abstract] = $instance;
    }

    /**
     * Check if a class instance is cached.
     *
     * @since 1.0.0
     *
     * @param string $abstract The class name to check
     * @return bool
     */
    protected function is_cached(string $abstract)
    {
        return isset(static::$instances[$abstract]);
    }

    /**
     * Get a cached class instance.
     *
     * @since 1.0.0
     *
     * @param string $abstract The class name to get
     * @return object
     */
    protected function get_cached(string $abstract)
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
     * $instance = $this->make(MyClass::class);
     */
    protected function make(string $abstract, array $resolving = [])
    {
        if ($this->is_cached($abstract)) {
            return $this->get_cached($abstract);
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

            $dependencies[] = $this->is_cached($type->getName())
                ? $this->get_cached($type->getName())
                : $this->make($type->getName(), $resolving);
        }

        $instance = $reflector->newInstanceArgs($dependencies);
        $this->cache($abstract, $instance);

        return $instance;
    }

    /**
     * Resolve the route handler.
     *
     * @since 1.0.0
     *
     * @return callable
     * @throws InvalidRoutActionException
     */
    protected function resolve_route()
    {
        return function ($rest_request) {
            $request = Request::from_wp_rest_request($rest_request);

            if (!is_array($this->action)) {
                /* translators: %s: Route endpoint */
                throw new InvalidRoutActionException(sprintf(esc_html__('Invalid method registered for the route %s', 'growfund'), esc_html($this->endpoint)));
            }

            if (count($this->action) !== 2) {
                /* translators: %s: Route endpoint */
                throw new InvalidRoutActionException(sprintf(esc_html__('Invalid controller syntax for the route %s', 'growfund'), esc_html($this->endpoint)));
            }

            list($controller, $method) = $this->action;

            if (!class_exists($controller)) {
                /* translators: %s: Controller class */
                throw new InvalidRoutActionException(sprintf(esc_html__('Controller %s not found', 'growfund'), esc_html($controller)));
            }

            $controller_instance = $this->make($controller);

            if (!method_exists($controller_instance, $method)) {
                /* translators: 1: Method name, 2: Controller class */
                throw new InvalidRoutActionException(sprintf(esc_html__('The method %1$s is missing in the controller %2$s', 'growfund'), esc_html($method), esc_html($controller)));
            }

            try {
                return $controller_instance->$method($request);
            } catch (Exception $exception) {
                return ApiExceptionHandler::get_response($exception);
            }
        };
    }
}
