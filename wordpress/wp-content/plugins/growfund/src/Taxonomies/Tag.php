<?php

namespace Growfund\Taxonomies;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;

/**
 * Class to register a custom taxonomy.
 *
 * @since 1.0.0
 */
class Tag implements Registrable
{
    /**
     * Taxonomy's unique name.
     */
    const NAME = 'growfund_campaign_tag';

    /**
     * Register a custom taxonomy.
     *
     * @return void
     */
    public function register()
    {
        $labels = array(
            'name'              => __('Tags', 'growfund'),
            'singular_name'     => __('Tag', 'growfund'),
        );

        $args = array(
            'labels' => $labels,
            'hierarchical' => false,
            'sort' => true,
            'args' => array('orderby' => 'term_order'),
            'rewrite' => array('slug' => 'growfund_tags'),
            'show_admin_column' => true
        );

        register_taxonomy(static::NAME, [\Growfund\PostTypes\Campaign::NAME], $args);
    }
}
