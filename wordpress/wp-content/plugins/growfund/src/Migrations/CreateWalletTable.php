<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;

/**
 * Class CreateWalletTable
 *
 * Handles the creation and removal of the `wallets` database table
 *
 * @since 1.1.0
 */
class CreateWalletTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::WALLETS;

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
            `user_id` BIGINT UNSIGNED NULL,
            `balance` INT NOT NULL DEFAULT 0,
            `requested_amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `withdraw_amount` INT UNSIGNED NOT NULL DEFAULT 0,
            `platform_fee` INT UNSIGNED NOT NULL DEFAULT 0,
            `updated_at` DATETIME NULL,
            FOREIGN KEY (`user_id`) REFERENCES `$users_table`(`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
            INDEX idx_user_id (`user_id`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `wallets` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
