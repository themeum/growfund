<?php

namespace Growfund\PostTypes;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Registrable;

/**
 * Class to register a custom post type for reward items.
 *
 * @since 1.0.0
 */
class RewardItem implements Registrable
{
    /**
     * Post type's unique name.
     */
    const NAME = 'growfund_reward_item';

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
            'name' => __('Reward Items', 'growfund'),
            'singular_name' => __('Reward Item', 'growfund'),
        ];

        $args = [
            'labels' => $labels,
            'has_archive' => false,
            'public' => false,
            'hierarchical' => false,
            'rewrite' => ['slug' => 'reward-items'],
            'supports' => [],
        ];

        register_post_type(self::NAME, $args);
    }
}
