<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\QueryBuilder;

/**
 * Class CreateWalletTransactionTable
 *
 * Handles the creation and removal of the `wallet_transactions` database table
 *
 * @since 1.1.0
 */
class CreateWalletTransactionTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::WALLET_TRANSACTIONS;

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

        $wallets_table = QueryBuilder::query()->prefix(Tables::WALLETS);

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `wallet_id` BIGINT UNSIGNED NOT NULL,
            `campaign_id` BIGINT UNSIGNED NULL,
            `reference_id` BIGINT UNSIGNED NULL COMMENT 'The pledge or donation or withdrawal_request ID',
            `reference_type` VARCHAR(30) NULL COMMENT 'pledge|donation|withdrawal_request',
            `amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `action` VARCHAR(20) NOT NULL COMMENT 'debit for earnings, credit for others',
            `type` VARCHAR(30) NOT NULL COMMENT 'earning|platform_fee|withdrawal_request|withdrawal_approval|withdrawal_rejection',
            `status` VARCHAR(20) NOT NULL DEFAULT 'pending' COMMENT 'pending|completed',
            `created_at` DATETIME NOT NULL,
            FOREIGN KEY (`wallet_id`) REFERENCES `$wallets_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_wallet_id_status (`wallet_id`, `status`),
            INDEX idx_status_type (`type`, `status`),
            INDEX idx_campaign_id (`campaign_id`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `wallet_transactions` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
