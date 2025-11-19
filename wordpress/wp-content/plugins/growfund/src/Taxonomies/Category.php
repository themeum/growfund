<?php

namespace Growfund\Taxonomies;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;

/**
 * Class to register a custom taxonomy.
 *
 * @since 1.0.0
 */
class Category implements Registrable
{
    /**
     * Taxonomy's unique name.
     */
    const NAME = 'growfund_campaign_category';


    const UNCATEGORIZED = 'Uncategorized';

    /**
     * Register a custom taxonomy.
     *
     * @return void
     */
    public function register()
    {
        $labels = array(
            'name'              => __('Categories', 'growfund'),
            'singular_name'     => __('Category', 'growfund'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => true,
            'sort' => true,
            'args' => array('orderby' => 'term_order'),
            'rewrite' => array('slug' => 'growfund_categories'),
            'show_admin_column' => true
        );

        register_taxonomy(static::NAME, [\Growfund\PostTypes\Campaign::NAME], $args);
    }
}
