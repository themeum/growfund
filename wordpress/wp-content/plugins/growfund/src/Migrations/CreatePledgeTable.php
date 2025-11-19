<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;

/**
 * Class CreatePledgeTable
 *
 * Handles the creation and removal of the `pledges` database table
 * for the Growfund plugin. This table stores pledge-related data
 * such as campaign references, payment details, and backer metadata.
 *
 * @since 1.0.0
 */
class CreatePledgeTable extends BaseMigration
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
     * Creates the `pledges` table with all necessary columns and constraints.
     * Includes campaign, backer, reward, payment, and metadata fields.
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
            `uid` VARCHAR(40) NOT NULL,
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NULL,
            `status` VARCHAR(255) NOT NULL,
            `pledge_option` VARCHAR(255) NOT NULL,
            `reward_id` BIGINT NULL,
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `bonus_support_amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `shipping_cost` INT UNSIGNED NOT NULL DEFAULT 0,
            `recovery_fee` INT UNSIGNED NOT NULL DEFAULT 0,
            `processing_fee` INT UNSIGNED NOT NULL DEFAULT 0,
            `should_deduct_fee_recovery` TINYINT NOT NULL DEFAULT 0,
            `notes` TEXT NULL,
            `transaction_id` VARCHAR(255) NULL,
            `payment_engine` VARCHAR(255) NULL,
            `payment_method` TEXT NULL,
            `payment_status` VARCHAR(255) NULL,
            `is_manual` TINYINT NOT NULL DEFAULT 0,
            `reward_info` TEXT NULL,
            `user_info` TEXT,
            `created_at` DATETIME NOT NULL,
            `created_by` BIGINT UNSIGNED NOT NULL,
            `updated_at` DATETIME NULL,
            `updated_by` BIGINT UNSIGNED NULL,

            UNIQUE KEY {$table_name}_unique_uid (`uid`),
            FOREIGN KEY (`campaign_id`) REFERENCES `$wpdb->posts`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `$wpdb->users`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;";

        $this->add_table($sql);
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
}
