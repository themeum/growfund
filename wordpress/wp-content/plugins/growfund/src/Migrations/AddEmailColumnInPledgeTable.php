<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;

/**
 * Class AddEmailColumnInPledgeTable
 *
 * @since 1.0.2
 */
class AddEmailColumnInPledgeTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::PLEDGES;

    /**
     * Run the migration.
     *
     * Adds the `email` column to the `pledges` table in the database.
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
            "SHOW COLUMNS FROM $table_name LIKE 'email'"
		);

        if (empty($column_exists)) {
            QueryBuilder::get_db()->query("ALTER TABLE `$table_name` ADD `email` VARCHAR(255) NULL AFTER `user_id`");
        }
    }

    /**
     * Reverse the migration.
     *
     * Remove the `email` column from the `pledges` table from the database.
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
            "SHOW COLUMNS FROM $table_name LIKE 'email'"
		);

        if (!empty($column_exists)) {
			QueryBuilder::get_db()->query("ALTER TABLE `$table_name` DROP COLUMN `email`");
        }
    }
}
