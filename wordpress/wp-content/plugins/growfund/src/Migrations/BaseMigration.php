<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\OptionKeys;
use Growfund\Contracts\Migration;
use Growfund\Supports\Option;

/**
 * Class BaseMigration
 *
 * Provides base functionality for database migrations in the Growfund plugin.
 * Implements common logic for creating and dropping database tables using WordPress dbDelta.
 *
 * @since 1.0.0
 */
abstract class BaseMigration implements Migration
{
    protected $table_name;

    /**
     * The plugin version number at which the migration becomes available. 
     */
    protected $is_available_at = '0.0.1';

    /**
     * Create a new instance of the migration class.
     *
     * @return static A new instance of the called class.
     */
    public static function instance()
    {
        return new static();
    }

    /**
     * Enable checking foreign key constraints
     *
     * @return bool
     */
    public function enable_checking_foreign_key_constraints()
    {
        global $wpdb;
        $sql = 'SET FOREIGN_KEY_CHECKS=1';

        return $wpdb->query($sql); // phpcs:ignore
    }

    /**
     * Disable checking foreign key constraints
     *
     * @return bool
     */
    public function disable_checking_foreign_key_constraints()
    {
        global $wpdb;
        $sql = 'SET FOREIGN_KEY_CHECKS=0';

        return $wpdb->query($sql); // phpcs:ignore
    }

    /**
     * Create a new database table if it doesn't exist.
     *
     * Uses WordPress dbDelta to safely create the table.
     *
     * @param string $sql        The SQL statement to create the table.
     *
     * @return void
     */
    protected function add_table($sql)
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Drop a database table if it exists.
     *
     * Prefixes the table name using $wpdb->prefix before executing.
     *
     * @param string $table_name The unprefixed name of the database table to drop.
     *
     * @return void
     */
	protected function drop_table($table_name)
    {
        global $wpdb;

        try {
            $this->disable_checking_foreign_key_constraints();
            return $wpdb->query("DROP TABLE IF EXISTS `{$table_name}`"); // phpcs:ignore
        } finally {
            $this->enable_checking_foreign_key_constraints();
        }
    }

    /**
     * Get the table name with wpdb prefix.
     *
     * @return string
     */
    protected function get_table_name()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . $this->table_name;

        return $table_name;
    }

    protected function is_available_for_current_version() {
        return version_compare(growfund_app()->installed_db_version(), $this->is_available_at, '<') ;
    }

    abstract public function up();

    abstract public function down();

    public function handle_up() {
        if (!$this->is_available_for_current_version() && $this->is_already_migrated()) {
            return;
        }

        $this->up();
        $this->mark_as_migrated();
    }

    public function handle_down() {
        if (!$this->is_already_migrated()) {
            return;
        }
        
        $this->down();

        $migrations = $this->get_completed_migrations();
        $migrations = array_diff($migrations, [static::class]);
        $this->set_completed_migrations($migrations);
    }

    /**
     * Check if the migration has already been run.
     */
    protected function is_already_migrated()
    {
        return in_array(static::class, $this->get_completed_migrations(), true);
    }

    protected function mark_as_migrated()
    {
        $migrations = $this->get_completed_migrations();
        $migrations[] = static::class;

        $this->set_completed_migrations($migrations);
    }

	protected function get_completed_migrations() {
        $migrations = Option::get(OptionKeys::DATABASE_MIGRATION_TRACKER, []);

        return $migrations;
    }

    protected function set_completed_migrations(array $migrations) {
        Option::update(OptionKeys::DATABASE_MIGRATION_TRACKER, $migrations);
    }
}
