<?php

namespace Growfund\PostTypes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;
use Growfund\Core\AppSettings;

/**
 * Class to register a custom post type for campaigns.
 *
 * @since 1.0.0
 */
class Campaign implements Registrable
{
    /**
     * Post type's unique name.
     */
    const NAME = 'growfund_campaign';

    /**
     * Default post status.
     */
    const DEFAULT_POST_STATUS = 'publish';

    /**
     * Register a custom post type.
     *
     * @return void
     */
    public function register()
    {
        $advance_settings = growfund_settings(AppSettings::ADVANCED)->get();

        $labels = [
            'name' => __('Campaigns', 'growfund'),
            'singular_name' => __('Campaign', 'growfund'),
        ];
        $args = [
            'labels' => $labels,
            'has_archive' => true,
            'public' => false,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => trim($advance_settings['campaign_permalink'] ?? 'campaigns', '/')],
            'supports' => ['title'],
            'show_in_nav_menus' => true,
        ];

        register_post_type(self::NAME, $args);
    }
}
