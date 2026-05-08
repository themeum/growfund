<?php 

namespace Growfund\Managers;

use Exception;
use Growfund\Contracts\Migration;

defined( 'ABSPATH' ) || exit;

class MigrationManager
{
    /**
     * Run the migrations.
     * 
     * Iterates over the migrations array and runs the `up` method on each
     * migration class. If the class does not exist or does not implement the
     * `Growfund\Contracts\Migration` interface, an exception is thrown.
     * 
     * @param Migration[]|string[] $executable_migrations The list of migration class names or class instance to run
     */
    public static function run(array $executable_migrations) 
    {
        if (empty($executable_migrations)) {
            return;
        }

        $executable_migrations = array_map(function ($migration) {
            if ($migration instanceof Migration) {
                return get_class($migration);
            }

            return is_string($migration) ? $migration : null;
        }, $executable_migrations);

        $migrations = growfund_app()->tagged('app.migrations');

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

            if (!in_array(get_class($migration), $executable_migrations, true)) {
                continue;
            }

            $migration->handle_up();
        }
    }
}
