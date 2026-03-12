<?php

namespace Growfund\Migrations;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\WP;
use Growfund\Services\CampaignCategoryService;
use Growfund\Supports\TermMeta;
use Growfund\Taxonomies\Category;

/**
 * Class InsertUncategorizedInTermsTable
 *
 * Handles insertion and removal of a row in the `wp_terms` database table
 * for the Growfund plugin. This table stores default category named `Uncategorized`.
 *
 * @since 1.0.0
 */
class InsertUncategorizedInTermsTable extends BaseMigration
{
    /**
     * The unprefixed name of the database table.
     *
     * @var string
     */
    protected $table_name = WP::TERM_TABLE;

    /**
     * Run the migration.
     *
     * Insert `Uncategorized` row into the terms table.
     *
     * @return void
     */
    public function up()
    {
        if (!taxonomy_exists(Category::NAME)) {
            (new Category())->register();
        }

        $category_service = new CampaignCategoryService();

        if (!empty($category_service->get_default_category_id())) {
            return;
        }

        $result = $category_service->create(['name' => Category::UNCATEGORIZED]);
        TermMeta::update($result['term_id'], growfund_with_prefix('is_default'), true);
    }

    /**
     * Reverse the migration.
     *
     * Drops the `Uncategorized` row from the terms table.
     *
     * @return void
     */
    public function down()
    {
        if (!taxonomy_exists(Category::NAME)) {
            (new Category())->register();
        }
        
        $category_service = new CampaignCategoryService();

        $default_category_id = $category_service->get_default_category_id();

        if (!empty($default_category_id)) {
            wp_delete_term($default_category_id, Category::NAME);
        }
    }
}
