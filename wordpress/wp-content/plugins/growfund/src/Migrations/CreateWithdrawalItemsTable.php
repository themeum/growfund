<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;

/**
 * Class CreateWithdrawalItemsTable
 *
 * Handles the creation and removal of the `withdrawal_items` database table
 *
 * @since 1.1.0
 */
class CreateWithdrawalItemsTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::WITHDRAWAL_ITEMS;

    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_table_name();

        $withdrawal_requests_table = QueryBuilder::query()->prefix(Tables::WITHDRAWAL_REQUESTS);

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `withdrawal_request_id` BIGINT UNSIGNED NOT NULL,
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            FOREIGN KEY (`withdrawal_request_id`) REFERENCES `$withdrawal_requests_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_withdrawal_campaign_id (`withdrawal_request_id`, `campaign_id`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `withdrawal_items` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
