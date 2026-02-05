<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Migration;

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
}
