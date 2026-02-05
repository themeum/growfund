<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionParameter;
use TypeError;
use Growfund\Contracts\Container as ContainerContract;
use LogicException;
use ReflectionNamedType;

class Container implements ContainerContract
{
    /**
     * The instance of the container.
     *
     * @var self|null
     */
    protected static $instance = null;

    /**
     * The bindings of the container.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The instances of the container (singletons).
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The aliases for services.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * Services currently being resolved (for circular dependency detection).
     *
     * @var array
     */
    protected $resolved = [];

    /**
     * Tagged services.
     *
     * @var array
     */
    protected $tags = [];

    /**
     * Bind a class to the container.
     *
     * @param string $name
     * @param Closure $resolver
     * @return void
     */
    public function bind(string $name, Closure $resolver)
    {
        if (!$resolver instanceof Closure) {
            throw new TypeError(sprintf(
                /* translators: %s: Container class name */ 
                esc_html__('%s::bind() expects a Closure as the second argument.', 'growfund'),
                self::class
            ));
        }

        $this->bindings[$name] = [
            'resolver' => $resolver,
            'singleton' => false
        ];
    }

    /**
     * Bind a class to the container as a singleton (lazy loaded).
     *
     * @param string $name
     * @param Closure $resolver
     * @return void
     */
    public function singleton(string $name, Closure $resolver)
    {
        if (!$resolver instanceof Closure) {
            throw new TypeError(sprintf(
                /* translators: %s: Container class name */
                esc_html__('%s::singleton() expects a Closure as the second argument.', 'growfund'),
                self::class
            ));
        }

        $this->bindings[$name] = [
            'resolver' => $resolver,
            'singleton' => true
        ];
    }

    /**
     * Bind an existing instance to the container.
     *
     * @param string $name
     * @param mixed $instance
     * @return void
     */
    public function instance(string $name, $instance)
    {
        $this->instances[$name] = $instance;
    }

    /**
     * Create an alias for a service.
     *
     * @param string $alias
     * @param string $abstract
     * @return void
     */
    public function alias(string $alias, string $abstract)
    {
        if ($alias === $abstract) {
            /* translators: %s: abstract class name */
            throw new LogicException(sprintf(esc_html__('[%s] is aliased to itself.', 'growfund'), esc_html($abstract)));
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Tag services for grouped resolution.
     *
     * @param array $services
     * @param string $tag
     * @return void
     */
    public function tag(array $services, string $tag)
    {
        foreach ($services as $service) {
            $this->tags[$tag][] = $service;
        }
    }

    /**
     * Get all services with a given tag.
     *
     * @param string $tag
     * @return array
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        return array_map([$this, 'make'], $this->tags[$tag]);
    }

    /**
     * Make a class from the container.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    public function make(string $name, array $parameters = [])
    {
        return $this->resolve($name, $parameters);
    }

    /**
     * Resolve a class from the container.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    protected function resolve(string $name, array $parameters = [])
    {
        // Resolve aliases
        $name = $this->get_alias($name);

        // Return existing singleton instance
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Use manual binding if available
        if (isset($this->bindings[$name])) {
            return $this->resolve_binding($name, $parameters);
        }

        // Attempt automatic resolution
        return $this->autowire($name, $parameters);
    }

    /**
     * Resolve a manually bound service.
     *
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    protected function resolve_binding(string $name, array $parameters = [])
    {
        $binding = $this->bindings[$name];
        $instance = $binding['resolver']($this, $parameters);

        // Store singleton instances
        if ($binding['singleton']) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Automatically wire dependencies using reflection.
     *
     * @param string $class
     * @param array $parameters
     * @return mixed
     */
    protected function autowire(string $class, array $parameters = [])
    {
        // Check for circular dependencies
        if (in_array($class, $this->resolved, true)) {
            $chain = implode(' → ', $this->resolved) . " → {$class}";
            throw new Exception(sprintf(
                /* translators: %s: Dependency chain */
                esc_html__('Circular dependency detected: %s', 'growfund'),
                esc_html($chain)
            ));
        }

        $this->resolved[] = $class;

        try {
            $reflector = new ReflectionClass($class);

            if (!$reflector->isInstantiable()) {
                throw new Exception(sprintf(
                    /* translators: %s: Class name */
                    __('Class "%s" is not instantiable.', 'growfund'),
                    $class
                ));
            }

            $constructor = $reflector->getConstructor();

            if (!$constructor) {
                return new $class();
            }

            $dependencies = $this->resolve_dependencies($constructor->getParameters(), $parameters);

            return $reflector->newInstanceArgs($dependencies);
        } finally {
            array_pop($this->resolved);
        }
    }

    /**
     * Resolve constructor dependencies.
     *
     * @param array $parameters
     * @param array $primitives
     * @return array
     */
    protected function resolve_dependencies(array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getType();

            if ($dependency === null || $dependency->isBuiltin()) {
                // Handle primitive parameters
                $dependencies[] = $this->resolve_primitive($parameter, $primitives);
            } else {
                // Handle class parameters
                $dependency_name = $dependency instanceof ReflectionNamedType ? $dependency->getName() : (string) $dependency;
                $dependencies[] = $this->make($dependency_name);
            }
        }

        return $dependencies;
    }

    /**
     * Resolve primitive parameter.
     *
     * @param ReflectionParameter $parameter
     * @param array $primitives
     * @return mixed
     */
    protected function resolve_primitive(ReflectionParameter $parameter, array $primitives)
    {
        $paramName = $parameter->getName();

        if (array_key_exists($paramName, $primitives)) {
            return $primitives[$paramName];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception(sprintf(
            /* translators: 1: Parameter name, 2: Class name */
            esc_html__('Unable to resolve primitive parameter "%1$s" in class "%2$s".', 'growfund'),
            esc_html($paramName),
            esc_html($parameter->getDeclaringClass()->getName())
        ));
    }

    /**
     * Get the alias for a service.
     *
     * @param string $name
     * @return string
     */
    protected function get_alias(string $name): string
    {
        return $this->aliases[$name] ?? $name;
    }

    /**
     * Check if a class exists in the container.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        $name = $this->get_alias($name);

        return isset($this->bindings[$name]) ||
            isset($this->instances[$name]) ||
            class_exists($name);
    }

    /**
     * Get the instance of the container.
     *
     * @return static
     */
    public static function get_instance(): self
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Flush all bindings and instances.
     *
     * @return void
     */
    public function flush()
    {
        $this->bindings = [];
        $this->instances = [];
        $this->aliases = [];
        $this->resolved = [];
        $this->tags = [];
    }
}
