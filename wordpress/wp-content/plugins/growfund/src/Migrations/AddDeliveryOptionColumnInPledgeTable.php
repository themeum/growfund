<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;

/**
 * Class AddDeliveryOptionColumnInPledgeTable
 *
 * @since 1.0.9
 */
class AddDeliveryOptionColumnInPledgeTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::PLEDGES;

    protected $is_available_at = '1.0.9';

    /**
     * Run the migration.
     *
     * Adds the `delivery_option` and `is_ready_for_pickup` column to the `pledges` table in the database.
     *
     * @return void
     */
    public function up()
    {
        $table_name = $this->get_table_name();

        $result = QueryBuilder::get_db()->get_var(
            QueryBuilder::get_db()->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
		);

        $table_exists = $result === $table_name;

        if (!$table_exists) {
            return;
        }

        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'delivery_option'"
		);

        if (empty($column_exists)) {
            QueryBuilder::get_db()->query("
                ALTER TABLE `$table_name` 
                ADD `delivery_option` VARCHAR(25) DEFAULT 'home-delivery' AFTER `is_manual`;
            ");
        }

        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'is_ready_for_pickup'"
		);

        if (empty($column_exists)) {
            QueryBuilder::get_db()->query("
                ALTER TABLE `$table_name` 
                ADD `is_ready_for_pickup` TINYINT NOT NULL DEFAULT 0 AFTER `delivery_option`;
            ");
        }
    }

    /**
     * Reverse the migration.
     *
     * Remove the `delivery_option` and `is_ready_for_pickup` column from the `pledges` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $table_name = $this->get_table_name();

        $result = QueryBuilder::get_db()->get_var(
            QueryBuilder::get_db()->prepare(
                "SHOW TABLES LIKE %s",
                $table_name
            )
		);

        $table_exists = $result === $table_name;

        if (!$table_exists) {
            return;
        }
        
        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'delivery_option'"
		);

        if (!empty($column_exists)) {
			QueryBuilder::get_db()->query("
                ALTER TABLE `$table_name` 
                DROP COLUMN `delivery_option`;
            ");
        }

        $column_exists = QueryBuilder::get_db()->get_var(
            "SHOW COLUMNS FROM $table_name LIKE 'is_ready_for_pickup'"
		);

        if (!empty($column_exists)) {
			QueryBuilder::get_db()->query("
                ALTER TABLE `$table_name` 
                DROP COLUMN `is_ready_for_pickup`;
            ");
        }
    }
}
