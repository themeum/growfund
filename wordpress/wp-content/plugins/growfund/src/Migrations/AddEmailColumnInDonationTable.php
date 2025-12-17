<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;
use Growfund\Supports\Option;

/**
 * Class AddEmailColumnInDonationTable
 *
 * @since 1.0.2
 */
class AddEmailColumnInDonationTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::DONATIONS;

    /**
     * Run the migration.
     *
     * Adds the `email` column to the `donations` table in the database.
     *
     * @return void
     */
    public function up()
    {
        if ($this->has_email_column()) {
            return;
        }

        $table_name = $this->get_table_name();

        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'email'"
		);

        if (empty($column_exists)) {
            QueryBuilder::get_db()->query("ALTER TABLE `$table_name` ADD `email` VARCHAR(255) NULL AFTER `user_id`");
        }   
        Option::set(growfund_with_prefix('is_already_added_email_column_in_donations_table'), '1');
    }

    /**
     * Reverse the migration.
     *
     * Remove the `email` column from the `donations` table from the database.
     *
     * @return void
     */
    public function down()
    {
        if (!$this->has_email_column()) {
            return;
        }

        $table_name = $this->get_table_name();

        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'email'"
		);

        if (empty($column_exists)) {
            QueryBuilder::get_db()->query("ALTER TABLE `$table_name` DROP COLUMN `email`");
        }

        Option::delete(GROWFUND_PREFIX . 'is_already_added_email_column_in_donations_table');
    }

    /**
     * Check if the `email` column exists in the `donations` table.
     * 
     * @return bool
     */
    protected function has_email_column()
    {
        $is_already_added_email_column = Option::get(GROWFUND_PREFIX . 'is_already_added_email_column_in_donations_table', '0');

        return (bool) $is_already_added_email_column;
    }
}
