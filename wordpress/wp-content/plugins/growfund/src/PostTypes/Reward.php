<?php

namespace Growfund\PostTypes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;

/**
 * Class to register a custom post type for rewards.
 *
 * @since 1.0.0
 */
class Reward implements Registrable
{
    /**
     * Post type's unique name.
     */
    const NAME = 'growfund_reward';

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
        $labels = [
            'name' => __('Rewards', 'growfund'),
            'singular_name' => __('Reward', 'growfund'),
        ];
        $args = [
            'labels' => $labels,
            'has_archive' => true,
            'public' => false,
            'hierarchical' => true,
            'rewrite' => ['slug' => 'rewards'],
            'supports' => [
                'title',
                'editor',
            ],
        ];

        register_post_type(static::NAME, $args);
    }
}
