<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignPostUpdateEvent;
use Growfund\PostTypes\CampaignPost;
use Growfund\Sanitizer;
use Exception;

/**
 * CampaignPostService class
 * @since 1.0.0
 */
class CampaignPostService
{
    /**
     * Create new campaign post update.
     * 
     * @param int $campaign_id
     * @param array $data
     * 
     * @return int $post_id
     */
    public function save(int $campaign_id, array $data)
    {
        $post_id = wp_insert_post([
            'post_type'    => CampaignPost::NAME,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'] ?? Sanitizer::apply_rule($data['title'], Sanitizer::TITLE),
            'post_content' => $data['description'] ?? '',
            'post_status'  => CampaignPost::DEFAULT_POST_STATUS,
            'post_author'  => get_current_user_id(),
            'post_parent'  => $campaign_id,
        ], true);

        if (is_wp_error($post_id)) {
            throw new Exception(esc_html($post_id->get_error_message()));
        }

        $image = $data['image'][0] ?? null;
        set_post_thumbnail($post_id, $image);

        growfund_event(new CampaignPostUpdateEvent($campaign_id, $post_id));

        return $post_id;
    }

    /**
     * Delete campaign post by parent id
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete_by_parent_id(int $id)
    {
        $posts = get_posts([
            'post_type' => CampaignPost::NAME,
            'post_parent' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        if (empty($posts)) {
            return true;
        }

        foreach ($posts as $item) {
            wp_delete_post($item->ID, true);
        }

        return true;
    }
}
