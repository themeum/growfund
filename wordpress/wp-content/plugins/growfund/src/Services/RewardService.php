<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Reward\QuantityType;
use Growfund\Constants\Reward\RewardType;
use Growfund\Constants\Reward\TimeLimitType;
use Growfund\DTO\RewardDTO;
use Growfund\Http\Response;
use Growfund\PostTypes\Reward as PostTypeReward;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Pagination as PaginationSupport;
use Growfund\Supports\Paginator;
use Growfund\Supports\PostMeta;
use Exception;
use WP_Post;
use WP_Query;

/**
 * RewardService class
 * @since 1.0.0
 */
class RewardService
{
    public function __construct() {}

    /**
     * Create new reward
     * 
     * @param int $campaign_id
     * @param RewardDTO $dto
     * 
     * @return int
     */
    public function create(int $campaign_id, RewardDTO $dto)
    {
        $reward_id = wp_insert_post([
            'post_type'    => PostTypeReward::NAME,
            'post_title'   => $dto->title,
            'post_content' => $dto->description ?? '',
            'post_status'  => PostTypeReward::DEFAULT_POST_STATUS,
            'post_author'  => get_current_user_id(),
            'post_parent'  => $campaign_id,
        ], true);

        if (is_wp_error($reward_id)) {
            throw new Exception(esc_html($reward_id->get_error_message()));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_id, $dto->image);
        }

        $dto->allow_local_pickup = $dto->allow_local_pickup ?? false;
        $dto->items = $dto->items ?? [];

        $meta_input = $dto->get_meta();

        PostMeta::add_many($reward_id, $meta_input);

        return $reward_id;
    }

    /**
     * Update existing reward
     * @param int $reward_id
     * @param RewardDTO $dto
     * @return bool
     */
    public function update(int $reward_id, RewardDTO $dto)
    {
        $reward = wp_update_post([
            'ID' => $reward_id,
            'post_title' => $dto->title,
            'post_content' => $dto->description,
        ]);

        if (is_wp_error($reward)) {
            throw new Exception(esc_html($reward->get_error_message()));
        }

        if (!empty($dto->image)) {
            set_post_thumbnail($reward_id, $dto->image);
        }

        $meta_input = $dto->get_meta();

        return PostMeta::update_many($reward_id, $meta_input);
    }

    /** 
     * Delete an existing reward post with associated metadata.
     *
     * @param int $id The ID of the reward post.
     * @return bool True if successfully updated the reward or false.
     */
    public function delete(int $id): bool
    {
        $reward = get_post($id);

        if (!$reward) {
            throw new Exception(esc_html__('Reward not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $deleted_reward = wp_delete_post($id, true);

        return !empty($deleted_reward);
    }

    /**
     * Delete reward by parent id
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete_by_parent_id(int $id)
    {
        $rewards = get_posts([
            'post_type' => PostTypeReward::NAME,
            'post_parent' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        if (empty($rewards)) {
            return true;
        }

        foreach ($rewards as $item) {
            $this->delete($item->ID);
        }

        return true;
    }

    /**
     * Get paginated list of rewards.
     *
     * @param int $campaign_id
     * @param array $params Associative array containing:
     *   - int    'limit'       Number of results per page.
     *   - int    'page'        Current page number.
     *   - string 'search'      Search keyword (by ID).
     * @return Paginator
     */
    public function paginated(int $campaign_id, array $params)
    {
        $limit = $params['limit'] ?? 10;
        $page = $params['page'] ?? 1;

        $query_args = [
            'post_type'      => PostTypeReward::NAME,
            'post_parent'    => $campaign_id,
            'posts_per_page' => $limit,
            'paged'          => $page,
        ];

        if (!empty($params['search'])) {
            $query_args['s'] = $params['search'];
        }

        $query = new WP_Query($query_args);

        $results = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $reward) {
                $results[] = $this->prepare_reward_dto($reward);
            }
        }

        $total = $query->get_total();
        $overall = PaginationSupport::get_overall_post_count(PostTypeReward::NAME, ['post_parent' => $campaign_id]);

        return Paginator::make_metadata(
            $results,
            $limit,
            $page,
            $total,
            $overall
        );
    }

    /**
     * Get all rewards for a campaign.
     * @param int $campaign_id
     * @return RewardDTO[]
     */
    public function get_all($campaign_id)
    {
        $rewards = get_posts([
            'post_type' => PostTypeReward::NAME,
            'post_parent' => $campaign_id,
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ]);

        return Arr::make($rewards)->map(function (WP_Post $reward) {
            return $this->prepare_reward_dto($reward);
        })->toArray();
    }

    /**
     * Get a reward by its id.
     * @param int $reward_id
     * @return RewardDTO
     */
    public function get_by_id($reward_id)
    {
        $reward = get_post($reward_id);

        if (!$reward) {
            throw new Exception(esc_html__('Reward not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_reward_dto($reward);
    }

    /**
     * Prepare the reward data into the expected format.
     * 
     * @param WP_Post $reward
     * @return RewardDTO
     */
    protected function prepare_reward_dto(WP_Post $reward)
    {
        $meta_data = PostMeta::get_all($reward->ID);
        $thumbnail_id = get_post_thumbnail_id($reward->ID);

        $reward_items = [];

        if (isset($meta_data['items'])) {
            $reward_item_lists = maybe_unserialize($meta_data['items']);

            if (is_array($reward_item_lists)) {
                $reward_items = (new RewardItemService())->get_rewards_with_quantity($reward_item_lists);
            }
        }

        $dto = new RewardDTO();

        $dto->id = (string) $reward->ID;
        $dto->title = $reward->post_title;
        $dto->amount = (int) $meta_data['amount'];
        $dto->description = $reward->post_content;
        $dto->image = $thumbnail_id !== false ? MediaAttachment::make($thumbnail_id) : null;
        $dto->quantity_type = $meta_data['quantity_type'] ?? QuantityType::UNLIMITED;
        $dto->quantity_limit = !empty($meta_data['quantity_limit'])
            ? (int) $meta_data['quantity_limit']
            : null;
        $dto->time_limit_type = $meta_data['time_limit_type'] ?? TimeLimitType::NO_LIMIT;
        $dto->limit_start_date = !empty($meta_data['limit_start_date'])
            ? $meta_data['limit_start_date']
            : null;
        $dto->limit_end_date = !empty($meta_data['limit_end_date'])
            ? $meta_data['limit_end_date']
            : null;
        $dto->reward_type = $meta_data['reward_type'] ?? RewardType::PHYSICAL_GOODS;
        $dto->estimated_delivery_date = !empty($meta_data['estimated_delivery_date'])
            ? $meta_data['estimated_delivery_date']
            : null;
        $dto->shipping_costs = isset($meta_data['shipping_costs'])
            ? maybe_unserialize($meta_data['shipping_costs'])
            : null;
        $dto->allow_local_pickup = isset($meta_data['allow_local_pickup'])
            ? filter_var($meta_data['allow_local_pickup'], FILTER_VALIDATE_BOOLEAN)
            : false;
        $dto->local_pickup_instructions = $meta_data['local_pickup_instructions'] ?? null;
        $dto->items = $reward_items;

        $total_reward_taken = (new PledgeService())->get_count_of_pledges_by_rewards($reward->ID);

        $dto->number_of_contributors = $total_reward_taken;
        $reward_left = 0;

        if ($dto->quantity_type === QuantityType::LIMITED && !is_null($dto->quantity_limit)) {
            $reward_left = $dto->quantity_limit - $total_reward_taken;
        }

        $dto->reward_left = $reward_left;

        return $dto;
    }
}
