<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;

/**
 * Class CreateCampaignCollaboratorTable
 *
 * @since 1.0.0
 */
class CreateCampaignCollaboratorTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::CAMPAIGN_COLLABORATORS;

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

        $posts_table = QueryBuilder::query()->table(WP::POSTS_TABLE)->get_table_name();
        $users_table = QueryBuilder::query()->table(WP::USERS_TABLE)->get_table_name();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `collaborator_id` BIGINT UNSIGNED NOT NULL,
            `created_at` DATETIME NOT NULL,
            PRIMARY KEY (`campaign_id`, `collaborator_id`),
            FOREIGN KEY (`campaign_id`) REFERENCES `$posts_table`(`ID`) ON UPDATE CASCADE ON DELETE CASCADE,
            FOREIGN KEY (`collaborator_id`) REFERENCES `$users_table`(`ID`) ON UPDATE CASCADE ON DELETE CASCADE
        ) $charset_collate;";

        $this->add_table($sql);
    }


    /**
     * Reverse the migration.
     *
     * Drop the table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
