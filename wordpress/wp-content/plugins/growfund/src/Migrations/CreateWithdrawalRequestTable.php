<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;

/**
 * Class CreateWithdrawalRequestTable
 *
 * Handles the creation and removal of the `withdrawal_requests` database table
 *
 * @since 1.1.0
 */
class CreateWithdrawalRequestTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::WITHDRAWAL_REQUESTS;

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

        $users_table = QueryBuilder::query()->table(WP::USERS_TABLE)->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `method` VARCHAR(50) NOT NULL,
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
            `note` TEXT NULL,
            `attachment` VARCHAR(255) NULL,
            `payout_info` Text NULL,
            `created_at` DATETIME NOT NULL,
            `updated_by` BIGINT UNSIGNED NULL,
            `updated_at` DATETIME NULL,
            FOREIGN KEY (`user_id`) REFERENCES `$users_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_user_id_status (`user_id`, `status`),
            INDEX idx_status (`status`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `withdrawal_requests` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
