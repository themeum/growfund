<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;

/**
 * Class CreateDonationTable
 *
 * @since 1.0.0
 */
class CreateDonationTable extends BaseMigration
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
     * @return void
     */
    public function up()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->get_table_name();
        $funds_table = QueryBuilder::query()->table(Tables::FUNDS)->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `uid` VARCHAR(40) NOT NULL,
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `fund_id` BIGINT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NULL,
            `amount` INT UNSIGNED NOT NULL,
            `recovery_fee` INT UNSIGNED NOT NULL DEFAULT 0,
            `processing_fee` INT UNSIGNED NOT NULL DEFAULT 0,
            `should_deduct_fee_recovery` TINYINT NOT NULL DEFAULT 0,
            `tribute_type` VARCHAR(255) NULL,
            `tribute_salutation` VARCHAR(255) NULL,
            `tribute_to` VARCHAR(255) NULL,
            `tribute_notification_type` VARCHAR(255) NULL,
            `tribute_notification_recipient_name` VARCHAR(255) NULL,
            `tribute_notification_recipient_phone` VARCHAR(255) NULL,
            `tribute_notification_recipient_email` VARCHAR(255) NULL,
            `tribute_notification_recipient_address` TEXT NULL,
            `notes` TEXT NULL,
            `status` VARCHAR(255) NOT NULL,
            `transaction_id` VARCHAR(255) NULL,
            `payment_engine` VARCHAR(255) NULL,
            `payment_method` TEXT NULL,
            `payment_status` VARCHAR(255) NULL,
            `is_anonymous` TINYINT NOT NULL DEFAULT 0,
            `is_manual` TINYINT NOT NULL DEFAULT 0,
            `user_info` TEXT,
            `created_at` DATETIME NOT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL,
            `updated_by` BIGINT UNSIGNED NULL,

            UNIQUE KEY {$table_name}_unique_uid (`uid`),
            FOREIGN KEY (`campaign_id`) REFERENCES `$wpdb->posts`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`fund_id`) REFERENCES `$funds_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `$wpdb->users`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
