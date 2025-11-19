<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\RewardItemDTO;
use Growfund\DTO\RewardItemWithQuantityDTO;
use Growfund\Http\Response;
use Growfund\PostTypes\RewardItem;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Exception;
use WP_Post;

class RewardItemService
{
    /**
     * Get all reward items by campaign id.
     * 
     * @param int $campaign_id - default 0
     * 
     * @return RewardItemDTO[]
     */
    public function get_all_by_campaign(int $campaign_id = 0)
    {
        $reward_items = get_posts([
            'post_type' => RewardItem::NAME,
            'post_parent' => $campaign_id,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        return Arr::make($reward_items)->map(function (WP_Post $reward_item) {
            $thumbnail_id = get_post_thumbnail_id($reward_item->ID);
            $dto = new RewardItemDTO();
            $dto->id = (string) $reward_item->ID;
            $dto->campaign_id = (string) $reward_item->post_parent;
            $dto->title = $reward_item->post_title;
            $dto->image = !empty($thumbnail_id) ? MediaAttachment::make($thumbnail_id) : null;
            $dto->created_at = $reward_item->post_date_gmt;

            return $dto;
        })->toArray();
    }

    /**
     * Store a new reward item.
     *
     * @param int $campaign_id
     * @param RewardItemDTO $dto
     * @return int
     * @throws Exception
     */
    public function store(int $campaign_id, RewardItemDTO $dto): int
    {
        $reward_item_id = wp_insert_post([
            'post_type'       => RewardItem::NAME,
            'post_title'      => $dto->title,
            'post_status'     => RewardItem::DEFAULT_POST_STATUS,
            'post_author'     => get_current_user_id(),
            'post_parent'     => $campaign_id,
        ], true);

        if (is_wp_error($reward_item_id)) {
            /* translators: %s: error message */
            throw new Exception(sprintf(esc_html__('Failed to create reward item: %s', 'growfund'), esc_html($reward_item_id->get_error_message())));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_item_id, $dto->image);
        }

        return $reward_item_id;
    }

    /**
     * Update campaign category
     * 
     * @param int $id
     * @param int $campaign_id
     * @param RewardItemDTO $dto
     * 
     * @return bool
     * @throws Exception
     */
    public function update(int $id, int $campaign_id, RewardItemDTO $dto)
    {
        $reward_item = get_post($id);

        if (!$reward_item || $reward_item->post_parent !== $campaign_id || $reward_item->post_type !== RewardItem::NAME) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $reward_item_id = wp_update_post([
            'ID'           => $id,
            'post_title'   => $dto->title,
            'post_parent'  => $campaign_id,
        ], true);

        if (is_wp_error($reward_item_id)) {
            throw new Exception(esc_html__('Failed to update the reward item', 'growfund'));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_item_id, $dto->image);
        }

        return true;
    }

    /**
     * Delete reward item
     * 
     * @param int $campaign_id
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $campaign_id, int $id)
    {
        $reward_item = get_post($id);

        if (!$reward_item || $reward_item->post_parent !== $campaign_id || $reward_item->post_type !== RewardItem::NAME) {
            throw new Exception(esc_html__('Reward item not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $result = wp_delete_post($id, true);

        if (!$result) {
            throw new Exception(esc_html__('Failed to delete the reward item', 'growfund'));
        }

        return !empty($result);
    }

    /**
     * Delete reward item by parent id
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete_by_parent_id(int $id)
    {
        $reward_items = get_posts([
            'post_type' => RewardItem::NAME,
            'post_parent' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        if (empty($reward_items)) {
            return true;
        }

        foreach ($reward_items as $item) {
            wp_delete_post($item->ID, true);
        }

        return true;
    }

    /**
     * Get reward item by ids
     * 
     * @param array{array{id:int,quantity:int}} $reward_item_lists
     * 
     * @return RewardItemWithQuantityDTO[]
     */
    public function get_rewards_with_quantity(array $reward_item_lists)
    {
        if (empty($reward_item_lists)) {
            return [];
        }

        // check if the reward item list is valid
        foreach ($reward_item_lists as $item) {
            if (!array_key_exists('id', $item) || !array_key_exists('quantity', $item)) {
                return [];
            }
        }

        $ids = array_column($reward_item_lists, 'id');
        $reward_item_quantity_list = array_column($reward_item_lists, 'quantity', 'id');

        $result = get_posts([
            'post_type' => RewardItem::NAME,
            'post__in' => $ids,
            'posts_per_page' => -1,
        ]);

        $reward_items = [];

        foreach ($result as $reward_item) {
            $thumbnail_id = get_post_thumbnail_id($reward_item->ID);
            $dto = new RewardItemWithQuantityDTO();
            $dto->id = (string) $reward_item->ID;
            $dto->campaign_id = (string) $reward_item->post_parent;
            $dto->title = $reward_item->post_title;
            $dto->image = !empty($thumbnail_id) ? MediaAttachment::make($thumbnail_id) : null;
            $dto->created_at = $reward_item->post_date_gmt;
            $dto->quantity = $reward_item_quantity_list[$reward_item->ID] ?? 0;

            $reward_items[] = $dto;
        }

        return $reward_items;
    }
}
