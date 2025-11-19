<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;

/**
 * Class CreateActivityTable
 *
 * Handles the creation and removal of the `activities` database table
 *
 * @since 1.0.0
 */
class CreateActivityTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::ACTIVITIES;

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

        $post_table = QueryBuilder::query()->table(WP::POSTS_TABLE)->get_table_name();
        $users_table = QueryBuilder::query()->table(WP::USERS_TABLE)->get_table_name();
        $pledges_table = QueryBuilder::query()->table(Tables::PLEDGES)->get_table_name();
        $donations_table = QueryBuilder::query()->table(Tables::DONATIONS)->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `type` VARCHAR(255) NOT NULL,
            `campaign_id` BIGINT UNSIGNED NULL,
            `pledge_id` BIGINT UNSIGNED NULL,
            `donation_id` BIGINT UNSIGNED NULL,
            `data` TEXT,
            `user_id` BIGINT UNSIGNED NULL,
            `created_by` BIGINT UNSIGNED NULL,
            `created_at` DATETIME NOT NULL,
            FOREIGN KEY (`campaign_id`) REFERENCES `$post_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `$users_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`pledge_id`) REFERENCES `$pledges_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`donation_id`) REFERENCES `$donations_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            INDEX idx_activity_type_campaign_id (`type`, `campaign_id`)
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
