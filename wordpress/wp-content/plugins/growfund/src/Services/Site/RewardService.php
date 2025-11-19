<?php

namespace Growfund\Services\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Site\Reward\RewardDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\Http\Response;
use Growfund\PostTypes\Reward as PostTypeReward;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Money;
use Growfund\Supports\PostMeta;
use Growfund\Services\RewardItemService;
use Growfund\QueryBuilder;
use Growfund\Constants\Tables;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Reward\QuantityType;
use Growfund\DTO\Site\Reward\RewardItemDisplayDTO;
use Growfund\DTO\Site\OrderSummaryDTO;
use Growfund\Supports\UserMeta;
use Growfund\Constants\MetaKeys\Backer;
use Growfund\Supports\PriceCalculator;
use Exception;
use WP_Post;
use WP_Query;

/**
 * RewardService class
 * 
 * This service follows the Single Responsibility Principle by handling all reward-related
 * business logic and data transformation. The service layer is responsible for:
 * 
 * 1. Data retrieval and pagination
 * 2. Data transformation for frontend display
 * 3. Business logic processing
 * 4. Formatting and presentation logic
 * 
 * The DTO layer is kept clean and focused on data transfer only, with minimal
 * processing logic. This separation improves maintainability and testability.
 * 
 * @since 1.0.0
 */
class RewardService
{
    private $reward_item_service;

    public function __construct()
    {
        $this->reward_item_service = new RewardItemService();
    }

    /**
     * Get paginated rewards for a campaign.
     * @param int $campaign_id
     * @param int $page
     * @param int $limit
     * @return PaginatedCollectionDTO
     */
    public function paginated($campaign_id, $page = 1, $limit = 6)
    {
        $args = [
            'post_type' => PostTypeReward::NAME,
            'post_parent' => $campaign_id,
            'posts_per_page' => $limit,
            'paged' => $page,
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ];
        $query = new WP_Query($args);
        $rewards = [];

        if (!$query->have_posts()) {
            $paginated_dto = new PaginatedCollectionDTO();
            $paginated_dto->results = [];
            $paginated_dto->has_more = false;
            $paginated_dto->total = 0;
            $paginated_dto->current_page = $page;
            $paginated_dto->per_page = $limit;
            $paginated_dto->count = 0;
            $paginated_dto->overall = 0;
            return $paginated_dto;
        }

        while ($query->have_posts()) {
            $query->the_post();
            $reward = get_post();
            $reward_dto = $this->prepare_reward_dto($reward);

            // Always transform for frontend since this is a Site service
            $reward_dto = $this->transform_for_frontend($reward_dto);
            $rewards[] = $reward_dto;
        }

        $paginated_dto = new PaginatedCollectionDTO();
        $paginated_dto->results = $rewards;
        $paginated_dto->has_more = $query->max_num_pages > $page;
        $paginated_dto->total = $query->found_posts;
        $paginated_dto->current_page = $page;
        $paginated_dto->per_page = $limit;
        $paginated_dto->count = count($rewards);
        $paginated_dto->overall = $query->found_posts;

        return $paginated_dto;
    }

    /**
     * Get total count of rewards for a campaign.
     * @param int $campaign_id
     * @return int
     */
    public function get_rewards_count($campaign_id)
    {
        $args = [
            'post_type' => PostTypeReward::NAME,
            'post_parent' => $campaign_id,
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $query = new WP_Query($args);
        return $query->found_posts;
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

        $reward_items = $this->get_reward_items($reward->ID, $meta_data);
        $backers_count = $this->get_reward_backers_count($reward->ID);

        $dto = new RewardDTO();

        $dto->id = (string) $reward->ID;
        $dto->title = $reward->post_title;
        $dto->campaign_id = $reward->post_parent;
        $dto->amount = Money::prepare_for_display($meta_data['amount'] ?? 0);
        $dto->description = $reward->post_content;
        $dto->image = $thumbnail_id !== false ? MediaAttachment::make($thumbnail_id) : null;
        $dto->quantity_type = $meta_data['quantity_type'] ?? null;
        $dto->quantity_limit = $meta_data['quantity_limit'] ?? null;
        $dto->time_limit_type = $meta_data['time_limit_type'] ?? null;
        $dto->limit_start_date = !empty($meta_data['limit_start_date'])
            ? $meta_data['limit_start_date']
            : null;
        $dto->limit_end_date = !empty($meta_data['limit_end_date'])
            ? $meta_data['limit_end_date']
            : null;
        $dto->reward_type = $meta_data['reward_type'] ?? null;
        $dto->estimated_delivery_date = !empty($meta_data['estimated_delivery_date'])
            ? $meta_data['estimated_delivery_date']
            : null;
        $dto->shipping_costs = isset($meta_data['shipping_costs']) ? maybe_unserialize($meta_data['shipping_costs']) : null;
        $dto->allow_local_pickup = isset($meta_data['allow_local_pickup']) ? filter_var($meta_data['allow_local_pickup'], FILTER_VALIDATE_BOOLEAN) : false;
        $dto->local_pickup_instructions = $meta_data['local_pickup_instructions'] ?? null;
        $dto->items = $reward_items;
        $dto->backers = (string) ($backers_count ?? 0);

        return $dto;
    }

    /**
     * Get reward items for a reward
     * 
     * @param int $reward_id
     * @param array $meta_data
     * @return RewardItemDTO[]
     */
    protected function get_reward_items($reward_id, $meta_data)
    {
        if (!isset($meta_data['items'])) {
            return [];
        }

        $reward_item_lists = maybe_unserialize($meta_data['items']);

        if (!is_array($reward_item_lists)) {
            return [];
        }

        return $this->reward_item_service->get_rewards_with_quantity($reward_item_lists);
    }

    /**
     * Transform reward data for frontend display
     * 
     * @param RewardDTO $reward_dto
     * @return RewardDTO
     */
    public function transform_for_frontend(RewardDTO $reward_dto): RewardDTO
    {
        $frontend_dto = new RewardDTO();

        $frontend_dto->id = $reward_dto->id;
        $frontend_dto->title = $reward_dto->title;
        $frontend_dto->description = $reward_dto->description;
        $frontend_dto->amount = $reward_dto->amount;
        $frontend_dto->shipping_costs = $reward_dto->shipping_costs;
        $frontend_dto->campaign_id = $reward_dto->campaign_id;

        $frontend_dto->image_src = $this->get_image_src($reward_dto);
        $frontend_dto->image_alt = $this->get_image_alt($reward_dto);
        $frontend_dto->price = $reward_dto->amount ?? 0;
        $frontend_dto->ships_to = $this->get_shipping_info($reward_dto);
        $frontend_dto->backers = (string) ($reward_dto->backers ?? 0);
        $frontend_dto->delivery_date = $this->format_delivery_date($reward_dto->estimated_delivery_date ?? '');
        $frontend_dto->quantity_info = $this->get_quantity_info($reward_dto);
        $frontend_dto->items = $this->format_reward_items($reward_dto->items ?? []);


        return $frontend_dto;
    }

    /**
     * Get reward data for checkout page
     * 
     * @param int $reward_id
     * @return array
     */
    public function get_checkout_reward_data($reward_id)
    {
        $reward = $this->get_by_id($reward_id);

        if (!$reward) {
            return [
                'reward_data' => null,
                'order_summary' => new OrderSummaryDTO([
                    'bonus_support' => 0,
                    'reward_price' => 0,
                    'shipping' => 0,
                    'total' => 0
                ])
            ];
        }

        $reward = $this->transform_for_frontend($reward);

        $reward->variant = 'checkout';

        $raw_amount = PostMeta::get($reward_id, 'amount') ?? 0;

        $price = is_numeric($raw_amount) ? (int) $raw_amount : 0;

        $user_id = get_current_user_id();
        $shipping_cost = $user_id ? PriceCalculator::calculate_shipping_cost(UserMeta::get($user_id, Backer::SHIPPING_ADDRESS), $reward) : 0;

        $orderSummary = new OrderSummaryDTO([
            'bonus_support' => 0,
            'reward_price' => $price,
            'shipping' => $shipping_cost,
            'total' => $price + $shipping_cost
        ]);

        return [
            'reward_data' => $reward,
            'order_summary' => $orderSummary
        ];
    }

    /**
     * Get the backers count for a specific reward
     * 
     * @param int $reward_id
     * @return int
     */
    protected function get_reward_backers_count($reward_id)
    {
        return QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('reward_id', $reward_id)
            ->where_in('status', [PledgeStatus::PENDING, PledgeStatus::COMPLETED, PledgeStatus::BACKED])
            ->count('DISTINCT user_id');
    }

    /**
     * Get image source URL
     * 
     * @param RewardDTO $reward_dto
     * @return string
     */
    protected function get_image_src(RewardDTO $reward_dto): string
    {
        return ($reward_dto->image && isset($reward_dto->image['url'])) ? $reward_dto->image['url'] : '';
    }

    /**
     * Get image alt text
     * 
     * @param RewardDTO $reward_dto
     * @return string
     */
    protected function get_image_alt(RewardDTO $reward_dto): string
    {
        return $reward_dto->title ?? __('Reward Image', 'growfund');
    }



    /**
     * Format delivery date for display
     * 
     * @param string $delivery_date
     * @return string
     */
    protected function format_delivery_date(string $delivery_date): string
    {
        if (!empty($delivery_date)) {
            if (preg_match('/^[A-Za-z]{3} \d{4}$/', $delivery_date)) {
                return $delivery_date;
            }
            $timestamp = strtotime($delivery_date);
            if ($timestamp) {
                return wp_date('M Y', $timestamp);
            }
        }
        return __('TBD', 'growfund');
    }

    /**
     * Get quantity information for display
     * 
     * @param RewardDTO $reward_dto
     * @return string
     */
    protected function get_quantity_info(RewardDTO $reward_dto): string
    {
        if ($reward_dto->quantity_type === QuantityType::LIMITED && $reward_dto->quantity_limit) {
            /* translators: %d: reward quantity limit */
            return sprintf(__('%d available', 'growfund'), $reward_dto->quantity_limit);
        }
        return __('Unlimited', 'growfund');
    }

    /**
     * Get shipping information for display
     * 
     * @param RewardDTO $reward_dto
     * @return string
     */
    protected function get_shipping_info(RewardDTO $reward_dto): string
    {
        if ($reward_dto->shipping_costs && is_array($reward_dto->shipping_costs)) {
            $locations = array_column($reward_dto->shipping_costs, 'location');
            if (!empty($locations)) {
                return implode(', ', $locations);
            }
        }
        return __('Worldwide', 'growfund');
    }

    /**
     * Format reward items for frontend display using DTO structure
     * 
     * @param RewardItemDTO[] $items
     * @return RewardItemDisplayDTO[]
     */
    protected function format_reward_items(array $items): array
    {
        $formatted_items = [];
        if (!empty($items)) {
            foreach ($items as $item) {
                $reward_item_dto = new RewardItemDisplayDTO();

                $reward_item_dto->name = $item->title ?? __('Unknown Item', 'growfund');
                $reward_item_dto->quantity = (string) ($item->quantity ?? 1);
                $reward_item_dto->image_src = ($item->image && isset($item->image['url'])) ? $item->image['url'] : '';
                $reward_item_dto->image_alt = $item->title ?? __('Item Image', 'growfund');

                $formatted_items[] = $reward_item_dto;
            }
        }
        return $formatted_items;
    }
}
