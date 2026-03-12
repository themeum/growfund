<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\OptionKeys;
use Growfund\Contracts\Migration;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;

class ManageDatabaseMigrations extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $migrations = growfund_app()->tagged('app.migrations');

        if (empty($migrations)) {
            return;
        }

        if (version_compare(growfund_app()->installed_db_version(), GROWFUND_VERSION, '>=')) {
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

            $migration->handle_up();
        }

        Option::update(OptionKeys::INSTALLED_DB_VERSION, GROWFUND_VERSION);
    }
}
