<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;

/**
 * Class CreateBookmarkTable
 *
 * Handles the creation and removal of the `bookmarks` database table
 *
 * @since 1.0.0
 */
class CreateBookmarkTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::BOOKMARKS;

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

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            FOREIGN KEY (`campaign_id`) REFERENCES `$post_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (`user_id`) REFERENCES `$users_table`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
            UNIQUE KEY `unique_bookmarks_campaign_user` (`campaign_id`, `user_id`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `bookmarks` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
