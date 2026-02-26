<?php

namespace Growfund\Providers;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Capability;
use Growfund\Core\Dispatcher;
use Growfund\Core\ServiceProvider;
use Exception;
use SplFileObject;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container.
     *
     * @return void
     * @since 1.0.0
     */
    public function register()
    {
        $this->register_environment_variables();
        $this->register_migrations();
        $this->register_activation_actions();
        $this->register_deactivation_actions();
        $this->register_uninstallation_actions();
        $this->register_capabilities();
        $this->register_event_dispatcher();
    }

    /**
     * Boot services after all providers have been registered.
     *
     * @return void
     * @since 1.0.0
     */
    public function boot()
    {
        $capabilities = $this->app->tagged('app.capabilities');

		if (empty($capabilities)) {
			return;
		}

		foreach ($capabilities as $capability) {
			if (!$capability instanceof Capability) {
				throw new Exception(
				sprintf(
				/* translators: 1: Capability class name, 2: Capability interface name */
				esc_html__( 'Class %1$s must be a subclass of %2$s', 'growfund' ),
				esc_html( get_class( $capability ) ),
				esc_html( Capability::class )
				)
				);
			}

			$capability->handle();
		}
	}


    /**
     * Load environment variables from the .env file.
     * This will be used only for development stage.
     *
     * @return void
     */
    protected function register_environment_variables()
    {
        if (!growfund_is_dev_mode()) {
            return;
        }

        $root = growfund_input_server('DOCUMENT_ROOT', '');
        $env_file_path = sprintf('%s/.env', $root);

        if (!file_exists($env_file_path)) {
            return;
        }

        $env_file = new SplFileObject($env_file_path);

        while (!$env_file->eof()) {
            $line = trim($env_file->fgets());
            list($name, $value) = explode("=", $line);

            $value = trim($value, '"');
            $value = trim($value, "'");

            putenv(sprintf('%s=%s', $name, $value));
        }
    }

    /**
     * Register database migrations.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_migrations()
    {
        $migrations = require GROWFUND_DIR_PATH . 'configs/migrations.php';

        if (empty($migrations)) {
            return;
        }

        $this->app->tag($migrations, 'app.migrations');
    }

    /**
     * Register plugin activation actions.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_activation_actions()
    {
        $activations = require GROWFUND_DIR_PATH . 'configs/activations.php';

        if (empty($activations)) {
            return;
        }

        $this->app->tag($activations, 'app.activation-actions');
    }

    /**
     * Register plugin deactivation actions.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_deactivation_actions()
    {
        $deactivations = require GROWFUND_DIR_PATH . 'configs/deactivations.php';

        if (empty($deactivations)) {
            return;
        }

        $this->app->tag($deactivations, 'app.deactivation-actions');
    }

    /**
     * Register plugin uninstallation actions.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_uninstallation_actions()
    {
        $uninstallation = require GROWFUND_DIR_PATH . 'configs/uninstallation.php';

        if (empty($uninstallation)) {
            return;
        }

        $this->app->tag($uninstallation, 'app.uninstallation-actions');
    }

    /**
     * Register application capabilities.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_capabilities()
    {
        $capabilities  = require GROWFUND_DIR_PATH . 'configs/capabilities.php';

        if (empty($capabilities)) {
            return;
        }

        $this->app->tag($capabilities, 'app.capabilities');
    }

    /**
     * Register the event dispatcher service.
     *
     * @return void
     * @since 1.0.0
     */
    protected function register_event_dispatcher()
    {
        $this->app->singleton(Dispatcher::class, function () {
            return new Dispatcher();
        });

        $this->app->alias('event.dispatcher', Dispatcher::class);
    }
}
