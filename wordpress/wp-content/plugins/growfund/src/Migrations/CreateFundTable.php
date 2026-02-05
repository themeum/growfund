<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\Status\FundStatus;
use Growfund\Constants\Tables;
use Growfund\QueryBuilder;
use Growfund\Supports\Date;

/**
 * Class CreateFundTable
 *
 * Handles the creation and removal of the `funds` database table
 * for the Growfund plugin. This table stores funds.
 *
 * @since 1.0.0
 */
class CreateFundTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::FUNDS;

    /**
     * Run the migration.
     *
     * Creates the `timelines` table with all necessary columns and constraints.
     *
     * @return void
     */
    public function up()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` VARCHAR(255) NOT NULL,
            `description` MEDIUMTEXT NULL,
            `is_default` TINYINT NOT NULL DEFAULT 0,
            `status` VARCHAR(255) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL,
            `updated_by` BIGINT UNSIGNED NULL
        ) $charset_collate;";

        $this->add_table($sql);
        $this->create_db_trigger_for_prevent_row_delete($table_name);
        $this->insert_default_row();
    }

    /**
     * Reverse the migration.
     *
     * Drops the `pledges` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }

    /**
     * Create a database trigger to prevent row deletion.
     * @param string $table_name
     * @return void
     */
    protected function create_db_trigger_for_prevent_row_delete($table_name)
    {
        try {
            global $wpdb;
    
            $trigger_name = "prevent_{$table_name}_delete_row";
    
            $check_trigger_sql = $wpdb->prepare(
                "SELECT 1 FROM information_schema.TRIGGERS
                WHERE TRIGGER_SCHEMA = DATABASE()
                AND TRIGGER_NAME = %s",
                $trigger_name
            );
    
            if (!$wpdb->get_var($check_trigger_sql)) { // phpcs:ignore -- already prepared
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
                $sql = sprintf("CREATE TRIGGER %s BEFORE DELETE ON %s
                FOR EACH ROW
                BEGIN
                IF OLD.is_default = 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Row cannot be deleted';
                END IF;
                END
                ", $trigger_name, $table_name);
    
                $wpdb->query($sql); // phpcs:ignore
            }
        } catch (Exception $error) { // phpcs:ignore
            //ignore
        }
    }

    protected function insert_default_row()
    {
        $date_time = Date::current_sql_safe();

        $default_fund = QueryBuilder::query()->table($this->table_name)->where('is_default', 1)->first();

        if (empty($default_fund)) {
            QueryBuilder::query()->table($this->table_name)->create([
                'title' => 'General',
                'status' => FundStatus::PUBLISHED,
                'is_default' => 1,
                'created_at' => $date_time,
                'created_by' => 0,
                'updated_at' => $date_time,
                'updated_by' => 0
            ]);
        }
    }
}
