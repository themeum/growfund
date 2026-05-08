<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;

/**
 * Class CreateCampaignSnapshotTable
 *
 * Handles the creation and removal of the `campaign_snapshots` database table
 *
 * @since 1.1.0
 */
class CreateCampaignSnapshotTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = Tables::CAMPAIGN_SNAPSHOTS;

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

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            `ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `campaign_id` BIGINT UNSIGNED NOT NULL,
            `snapshot` TEXT NOT NULL,
            `updated_at` DATETIME NULL,
            INDEX idx_campaign_id (`campaign_id`)
        ) $charset_collate;";

        $this->add_table($sql);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `campaign_snapshots` table from the database.
     *
     * @return void
     */
    public function down()
    {
        $this->drop_table($this->get_table_name());
    }
}
