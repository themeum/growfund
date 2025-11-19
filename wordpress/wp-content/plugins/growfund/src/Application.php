<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\TransientKeys;
use Growfund\Contracts\Action;
use Growfund\Contracts\Migration;
use Growfund\Core\ServiceProvider;
use Growfund\Core\User;
use Growfund\Core\FeatureManager;
use Growfund\Providers\AppServiceProvider;
use Growfund\Providers\HookServiceProvider;
use Growfund\Providers\PaymentServiceProvider;
use Growfund\Traits\Macroable;
use Exception;
use InvalidArgumentException;

/**
 * The main application class.
 * This class is responsible for bootstrapping the plugin and managing the plugin lifecycle.
 * 
 * @method bool is_donation_mode()
 * @method bool is_woocommerce_installed()
 * @method bool is_onboarding_completed()
 * @method bool is_feature_available(string $feature_name)
 * @method bool is_migration_available_from_crowdfunding()
 */
class Application extends Container
{
    use Macroable;

    /**
     * Migrations
     * @var array<string>
     */
    protected $migrations = [];

    /**
     * Activation actions
     * @var array<string>
     */
    protected $activation_actions = [];

    /**
     * Deactivation actions
     * @var array<string>
     */
    protected $deactivation_actions = [];

    /**
     * Uninstall actions
     * @var array<string>
     */
    protected $uninstall_actions = [];

    /**
     * All the registered service providers.
     *
     * @var array<string, Growfund\Core\ServiceProvider>
     */
    protected $service_providers = [];

    /**
     * Mark the application booted or not
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Create a new application instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->register_core_bindings();
        $this->register_core_service_providers();
        $this->register_core_aliases();

        $this->register_app_defined_providers();
        $this->register_app_defined_aliases();
    }

    protected function register_core_bindings()
    {
        $this->instance('app', $this);
        $this->instance(Container::class, $this);

        $this->singleton(Renderer::class, function () {
            $renderer =  new Renderer();

            $plugin_layout_base_path = GROWFUND_WORKING_DIRECTORY . '/resources/views';
            $theme_layout_base_path = get_stylesheet_directory() . '/growfund';

            $renderer->set_plugin_layout_base_path($plugin_layout_base_path);
            $renderer->set_theme_layout_base_path($theme_layout_base_path);

            return $renderer;
        });
    }

    /**
     * Registering the core service providers to the application container.
     *
     * @return void
     */
    protected function register_core_service_providers()
    {
        $this->register(new AppServiceProvider($this));
        $this->register(new HookServiceProvider($this));
        $this->register(new PaymentServiceProvider($this));
    }

    /**
     * Register the core aliases for the registered dependencies.
     *
     * @return void
     */
    protected function register_core_aliases()
    {
        foreach (
            [
                'user' => [User::class],
                'renderer' => [Renderer::class],
                'mailer' => [Mailer::class],
                'features' => [FeatureManager::class]
            ] as $key => $aliases
        ) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Register a service provider to the application.
     *
     * @param ServiceProvider|string $provider
     * @return ServiceProvider
     */
    public function register($provider)
    {
        $registered = $this->get_provider($provider);

        if ($registered) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = $this->resolve_provider($provider);
        }

        $provider->register();

        $this->mark_as_registered($provider);

        if ($this->is_booted()) {
            $this->boot_provider($provider);
        }

        return $provider;
    }

    protected function get_provider($provider)
    {
        $name = is_string($provider) ? $provider : get_class($provider);

        return $this->service_providers[$name] ?? null;
    }

    /**
     * Resolve service provider from provider class name.
     *
     * @param string $provider
     * @return \Growfund\Core\ServiceProvider
     */
    protected function resolve_provider(string $provider)
    {
        return new $provider($this);
    }

    /**
     * Mark the service provider as registered.
     *
     * @param ServiceProvider $provider
     * @return void
     */
    protected function mark_as_registered(ServiceProvider $provider)
    {
        $class = get_class($provider);

        $this->service_providers[$class] = $provider;
    }

    /**
     * Boot the service provider.
     *
     * @param ServiceProvider $provider
     * @return void
     */
    protected function boot_provider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            $provider->boot();
        }
    }

    /**
     * Check if the application is booted or not.
     *
     * @return bool
     */
    public function is_booted()
    {
        return $this->booted;
    }

    /**
     * Configure the application.
     *
     * @return self
     */
    public static function configure()
    {
        return static::get_instance();
    }

    /**
     * Boot the plugin application.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->is_booted()) {
            return;
        }

        array_walk($this->service_providers, function ($provider) {
            $this->boot_provider($provider);
        });

        $this->booted = true;
    }

    /**
     * Register other on demand provided providers.
     *
     * @return void
     */
    protected function register_app_defined_providers()
    {
        $providers  = require GROWFUND_DIR_PATH . '/bootstrap/providers.php';

        if (empty($providers)) {
            return;
        }

        foreach ($providers as $provider) {
            if (!class_exists($provider) || !is_subclass_of($provider, ServiceProvider::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        /* translators: 1: Provider class name, 2: ServiceProvider class name */
                        esc_html__('Class %1$s must be a subclass of %2$s.', 'growfund'),
                        esc_html($provider),
                        ServiceProvider::class
                    )
                );
            }

            $this->register(new $provider($this));
        }
    }

    /**
     * Register tha aliases defined at the application level.
     * Generally it will come from the bootstrap/aliases.php file.
     *
     * @return void
     */
    protected function register_app_defined_aliases()
    {
        $aliases = require GROWFUND_DIR_PATH . '/bootstrap/aliases.php';

        if (empty($aliases)) {
            return;
        }

        foreach ($aliases as $alias => $abstracts) {
            foreach ($abstracts as $abstract) {
                $this->alias($alias, $abstract);
            }
        }
    }

    /**
     * Handle plugin activation hook.
     * This method is called by WordPress during plugin activation.
     *
     * @return void
     */
    public static function handle_activation()
    {
        $instance = static::get_instance();
        $instance->activate_growfund();
    }

    /**
     * Handle plugin deactivation hook.
     * This method is called by WordPress during plugin deactivation.
     *
     * @return void
     */
    public static function handle_deactivation()
    {
        $instance = static::get_instance();
        $instance->deactivate_growfund();
    }

    /**
     * Handle plugin uninstall hook.
     * This method is called by WordPress during plugin uninstall.
     *
     * @return void
     */
    public static function handle_uninstall()
    {
        static::uninstall_growfund();
    }

    /**
     * Activate the growfund plugin.
     *
     * @return void
     */
    public function activate_growfund()
    {
        $this->run_migrations();
        $this->run_activation_actions();

        /**
         * On after activation store a transient for detecting the plugin is activated.
         * This will be removed automatically by WordPress after 30 seconds.
         */
        set_transient(TransientKeys::GROWFUND_GROWFUND_ACTIVATED, true, 30);
    }

    /**
     * Deactivate the growfund plugin.
     *
     * @return void
     */
    public function deactivate_growfund()
    {
        $this->run_deactivation_actions();
    }

    /**
     * Uninstall the growfund plugin and clean up the database.
     *
     * @return void
     */
    public static function uninstall_growfund()
    {
        $instance = static::get_instance();
        $instance->run_uninstall_actions();
    }

    /**
     * Register the shortcodes.
     *
     * @param array $shortcodes
     * @return self
     * @since 1.0.0
     */
    /**
     * Run the migrations.
     *
     * Iterates over the migrations array and runs the `up` method on each
     * migration class. If the class does not exist or does not implement the
     * `Growfund\Contracts\Migration` interface, an exception is thrown.
     *
     * @return void
     * @throws Exception
     */
    protected function run_migrations()
    {
        $migrations = $this->tagged('app.migrations');

        if (empty($migrations)) {
            return;
        }

        foreach ($migrations as $migration) {
            if (!$migration instanceof Migration) {
                throw new Exception(
                    sprintf(
                        /* translators: 1: Migration class name, 2: Migration interface name */
                        esc_html__('Class %1$s must implement %2$s.', 'growfund'),
                        esc_html(get_class($migration)),
                        Migration::class
                    )
                );
            }

            $migration->up();
        }
    }

    /**
     * Runs the given actions.
     *
     * Iterates over the given $actions array and runs the `handle` method on each
     * action class. If the class does not exist or does not implement the
     * `Growfund\Contracts\Action` interface, an exception is thrown.
     *
     * @param array $actions The list of action class names to run
     *
     * @return void
     * @throws Exception
     */
    protected function run_actions(array $actions)
    {
        if (empty($actions)) {
            return;
        }

        foreach ($actions as $action) {
            if (!$action instanceof Action) {
                throw new Exception(
                    sprintf(
                        /* translators: 1: Action class name, 2: Action interface name */
                        esc_html__('Class %1$s must be a subclass of %2$s', 'growfund'),
                        esc_html(get_class($action)),
                        Action::class
                    )
                );
            }

            $action->handle();
        }
    }

    /**
     * Runs the activation actions.
     *
     * @return void
     * @throws Exception
     */
    protected function run_activation_actions()
    {
        $activation_actions = $this->tagged('app.activation-actions');
        $this->run_actions($activation_actions);
    }

    /**
     * Runs the deactivation actions.
     *
     * @return void
     * @throws Exception
     */
    protected function run_deactivation_actions()
    {
        $deactivation_actions = $this->tagged('app.deactivation-actions');
        $this->run_actions($deactivation_actions);
    }

    /**
     * Runs the uninstall actions.
     *
     * @return void
     * @throws Exception
     */
    protected function run_uninstall_actions()
    {
        $uninstallation_actions = $this->tagged('app.uninstallation-actions');
        $this->run_actions($uninstallation_actions);
    }
}
