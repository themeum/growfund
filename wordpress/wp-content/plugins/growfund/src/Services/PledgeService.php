<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignHalfMilestoneReachedEvent;
use Growfund\App\Events\GoalReachedEvent;
use Growfund\App\Events\PledgeCreatedEvent;
use Growfund\App\Events\PledgeStatusUpdateEvent;
use Growfund\Constants\AppreciationType;
use Growfund\Constants\Campaign\ReachingAction;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\Tables;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\Constants\MetaKeys\Campaign as MetaKeysCampaign;
use Growfund\Constants\PledgeOption;
use Growfund\Constants\Reward\QuantityType;
use Growfund\Constants\Reward\TimeLimitType;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Constants\Status\PaymentStatus;
use Growfund\Constants\WP;
use Growfund\DTO\BulkActionDTO;
use Growfund\DTO\Campaign\CampaignDTO;
use Growfund\DTO\Pledge\PledgeFilterParamsDTO;
use Growfund\DTO\Pledge\PledgeBackerDTO;
use Growfund\DTO\Pledge\PledgeCampaignDTO;
use Growfund\DTO\Pledge\PledgeDTO;
use Growfund\DTO\Pledge\PledgePaymentDTO;
use Growfund\DTO\Pledge\PledgeRewardDTO;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\RewardItemDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Payments\Constants\PaymentGatewayType;
use Growfund\Payments\DTO\PaymentMethodDTO;
use Growfund\PostTypes\Campaign;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Money;
use Growfund\Supports\PriceCalculator;
use DateTime;
use Exception;
use Growfund\Supports\Currency;

class PledgeService
{
    /**
     * Get pledges paginated data
     */
    public function paginated(PledgeFilterParamsDTO $params)
    {
        $query = $this->get_query($params);

        $overall_query_conditions = [];

        if ($params->user_id !== growfund_user()->get_id() && growfund_user()->is_fundraiser()) {
            $overall_query_conditions[] = ['campaign_id', 'IN', growfund_get_all_campaign_ids_by_fundraiser()];
        }

        if (!empty($params->user_id)) {
            $overall_query_conditions[] = ['user_id', '=', $params->user_id];
        }


        $paginated = $query->paginate($params->page, $params->limit, $overall_query_conditions);

        $results = $paginated['results'];

        foreach ($results as $key => $record) {
            $record->id = $record->ID;
            $paginated['results'][$key] = $this->get_formatted_data($record);
        }

        return $paginated;
    }

    /**
     * Get formatted data
     *
     * @param object $record
     * @return PledgeDTO
     */
    protected function get_formatted_data($record)
    {
        $payment = PledgePaymentDTO::from_array((array) $record);
        $payment->payment_method = PaymentMethodDTO::from_array(json_decode($record->payment_method ?? "", true) ?? []);

        $campaign_images = !empty($record->campaign_images) ? maybe_unserialize($record->campaign_images) : [];
        $campaign = PledgeCampaignDTO::from_array([
            'id' => (string) $record->campaign_id,
            'title' => $record->campaign_title,
            'slug' => $record->campaign_slug,
            'status' => $record->campaign_status,
            'fund_raised' => $this->get_total_pledges_amount_for_campaign($record->campaign_id),
            'goal_type' => $record->campaign_goal_type,
            'goal_amount' => $record->campaign_goal_amount,
            'images' => MediaAttachment::make_many($campaign_images),
            'start_date' => $record->campaign_start_date,
            'author_id' => $record->campaign_author_id,
            'created_by' => $record->campaign_created_by,
            'is_launched' => !empty($record->campaign_start_date) ? Date::is_date_in_past_or_present($record->campaign_start_date) : false,
        ]);

        $backer = null;

        if (growfund_is_valid_json($record->user_info)) {
            $user_info = json_decode($record->user_info, true);
            $user_info['id'] = (string) $user_info['id'];
            $user_info['image'] = !empty($user_info['image']) ? MediaAttachment::make($user_info['image']) : null;
            $user_info['is_verified'] = !empty($user_info['id']) && growfund_user($user_info['id'])->is_verified();
            $backer = PledgeBackerDTO::from_array($user_info);
        }

        $reward = null;

        if ($record->reward_id && growfund_is_valid_json($record->reward_info)) {
            $reward_info = json_decode($record->reward_info, true);
            $reward_info['image'] = !empty($reward_info['image']) ? MediaAttachment::make($reward_info['image']) : null;
            $reward_items = [];

            if (!empty($reward_info['items'])) {
                foreach ($reward_info['items'] as $item) {
                    $item['image'] = !empty($item['image']) ? MediaAttachment::make($item['image']) : null;
                    $reward_items[] = RewardItemDTO::from_array($item);
                }

                $reward_info['items'] = $reward_items;
            }

            $reward = PledgeRewardDTO::from_array($reward_info);
        }

        return PledgeDTO::from_array([
            'id' => (string) $record->ID,
            'uid' => $record->uid,
            'status' => $record->status,
            'is_manual' => (bool) $record->is_manual,
            'pledge_option' => $record->pledge_option,
            'notes' => $record->notes,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'payment' => $payment,
            'campaign' => $campaign,
            'backer' => $backer,
            'reward' => $reward,
        ]);
    }

    /**
     * Get query with applying conditions
     * @param PledgeFilterParamsDTO $params
     * @return QueryBuilder
     */
    protected function get_query(PledgeFilterParamsDTO $params)
    {
        $post_table = WP::POSTS_TABLE;
        $postmeta_table = WP::POST_META_TABLE;
        $user_table = WP::USERS_TABLE;

        $query = QueryBuilder::query()
            ->select([
                'pledges.*',
                'campaigns.post_title as campaign_title',
                'campaigns.post_name as campaign_slug',
                'campaign_status_meta.meta_value as campaign_status',
                'campaign_images_meta.meta_value as campaign_images',
                'campaign_start_date_meta.meta_value as campaign_start_date',
                'campaign_goal_amount_meta.meta_value as campaign_goal_amount',
                'campaign_goal_type_meta.meta_value as campaign_goal_type',
                'campaign_author.display_name as campaign_created_by',
                'campaign_author.ID as campaign_author_id',
            ])
            ->table(Tables::PLEDGES . ' as pledges')
            ->join("{$post_table} as campaigns", 'pledges.campaign_id', 'campaigns.ID')
            ->join_raw(
                "{$postmeta_table} as campaign_status_meta",
                "LEFT",
                sprintf("pledges.campaign_id = campaign_status_meta.post_id AND campaign_status_meta.meta_key = '%s'", growfund_with_prefix(MetaKeysCampaign::STATUS))
            )
            ->join_raw(
                "{$postmeta_table} as campaign_images_meta",
                "LEFT",
                sprintf("pledges.campaign_id = campaign_images_meta.post_id AND campaign_images_meta.meta_key = '%s'", growfund_with_prefix(MetaKeysCampaign::IMAGES))
            )
            ->join_raw(
                "{$postmeta_table} as campaign_start_date_meta",
                "LEFT",
                sprintf("pledges.campaign_id = campaign_start_date_meta.post_id AND campaign_start_date_meta.meta_key = '%s'", growfund_with_prefix(MetaKeysCampaign::START_DATE))
            )
            ->join_raw(
                "{$postmeta_table} as campaign_goal_amount_meta",
                "LEFT",
                sprintf("pledges.campaign_id = campaign_goal_amount_meta.post_id AND campaign_goal_amount_meta.meta_key = '%s'", growfund_with_prefix(MetaKeysCampaign::GOAL_AMOUNT))
            )
            ->join_raw(
                "{$postmeta_table} as campaign_goal_type_meta",
                "LEFT",
                sprintf("pledges.campaign_id = campaign_goal_type_meta.post_id AND campaign_goal_type_meta.meta_key = '%s'", growfund_with_prefix(MetaKeysCampaign::GOAL_TYPE))
            )
            ->join_raw(
                "{$user_table} as campaign_author",
                "LEFT",
                sprintf("campaigns.post_author = campaign_author.ID")
            );

        if (!empty($params->user_id) && $params->user_id !== growfund_user()->get_id() && growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('pledges.campaign_id', $campaign_ids);
        }

        if (!empty($params->user_id)) {
            $query->where('pledges.user_id', $params->user_id);
        }

        if (!empty($params->campaign_id)) {
            $query->where('pledges.campaign_id', $params->campaign_id)
                ->where('campaigns.post_type', Campaign::NAME);
        }

        if (!empty($params->search)) {
            $query->where('campaigns.post_title', 'LIKE', '%' . $params->search . '%');
        }

        if (!empty($params->status)) {
            if (is_array($params->status)) {
                $query->where_in('pledges.status', $params->status);
            } else {
                $query->where('pledges.status', $params->status);
            }
        }

        if (empty($params->status)) {
            $query->where('pledges.status', '!=', PledgeStatus::TRASHED);
        }

        if ($params->start_date) {
            $query->where('DATE(pledges.created_at)', '>=', $params->start_date);
        }

        if ($params->end_date) {
            $query->where('DATE(pledges.created_at)', '<=', $params->end_date);
        }

        $query->order_by('pledges.created_at', 'DESC');

        return $query;
    }

    /** 
     * Get the total amount pledged by a user.
     * We are calling a pledge pledged if the pledge status is Pending, Backed or Completed.
     * 
     * @param int $backer_id
     * 
     * @return int
     */
    public function get_total_pledges_amount(int $backer_id)
    {
        $amount = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in(
                'status',
                [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            )
            ->sum('amount + bonus_support_amount + shipping_cost');

        return $amount ? (int) $amount : 0;
    }


    /**
     * Get the total backed amount by a backer.
     * We are calling a pledge backed if the pledge status is Backed or Completed.
     *
     * @param integer $backer_id
     * @return int
     */
    public function get_total_backed_amount(int $backer_id)
    {
        $amount = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in(
                'status',
                [PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            )
            ->sum('amount + bonus_support_amount + shipping_cost');

        return $amount ? (int) $amount : 0;
    }

    /**
     * Get the total backed amount for campaign.
     * We are calling a pledge backed if the pledge status is Backed or Completed.
     * 
     * @param int|null $campaign_id The campaign ID. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return int
     */
    public function get_total_backed_amount_for_campaign($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in(
                'status',
                [PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            );

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $amount = $query->sum('amount + bonus_support_amount + shipping_cost');

        return $amount ? (int) $amount : 0;
    }

    /**
     * Get the net backed amount for campaign.
     * We are calling a pledge backed if the pledge status is Backed or Completed.
     * 
     * @param int|null $campaign_id The campaign ID. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return void
     */
    public function get_net_backed_amount_for_campaign($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in(
                'status',
                [PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            );

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $amount = $query->sum('amount + bonus_support_amount + shipping_cost - processing_fee');

        return $amount ? (int) $amount : 0;
    }

    /**
     * Get the average backed amount for campaign.
     * We are calling a pledge backed if the pledge status is Backed or Completed.
     * 
     * @param int|null $campaign_id The campaign ID. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * 
     * @return void
     */
    public function get_average_backed_amount_for_campaign($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in(
                'status',
                [PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            );

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $amount = $query->avg('amount + bonus_support_amount + shipping_cost');

        return $amount ? (int) $amount : 0;
    }


    /**
     * Get the largest pledge amount by a user.
     * 
     * @param int $backer_id
     * 
     * @return int
     */
    public function get_largest_pledge_amount(int $backer_id)
    {
        $largest_amount = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in('status', [PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->max('amount + bonus_support_amount + shipping_cost');

        return $largest_amount ? (int) $largest_amount : 0;
    }

    /**
     * Get the latest pledge by a user.
     * 
     * @param int $backer_id
     * 
     * @return object|null
     */
    public function get_latest_pledge(int $backer_id)
    {
        return QueryBuilder::query()->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in('status', [PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->order_by('created_at', 'DESC')
            ->first();
    }

    /**
     * Get the latest pledge date of a backer
     * @since 1.0.3
     * @param int $backer_id
     * @return string|null
     */
    public function get_latest_pledge_date(int $backer_id) {
		$pledge = QueryBuilder::query()->table(Tables::PLEDGES . ' as pledges')
            ->select([
                'pledges.id',
                'pledges.created_at',
            ])  
            ->where('pledges.user_id', $backer_id)
            ->where_in('pledges.status', [PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->order_by('pledges.created_at', 'DESC')
            ->first();

        return $pledge->created_at ?? null;
    }

    /**
     * Get the total number of pledges counts.
     * 
     * @param int $backer_id
     * 
     * @return int
     */
    public function get_total_number_of_pledges(int $backer_id)
    {
        $pledges = QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in(
                'status',
                [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            )
            ->count();

        return $pledges;
    }

    /**
     * Get the total count of refunded pledges for a backer.
     *
     * @param int $backer_id
     * @return int
     */
    public function get_total_number_of_refunded_pledges(int $backer_id)
    {
        $total_refunded = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where('status', PledgeStatus::REFUNDED)
            ->count();

        return $total_refunded;
    }

    /**
     * Get the number of pledged campaign by a backer.
     * And the pledge status is PENDING or IN_PROGRESS or BACKED or COMPLETED.
     *
     * @param int $backer_id
     * @return int
     */
    public function get_pledged_campaigns_by_backer(int $backer_id)
    {
        $campaigns = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in('status', [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->where('user_id', $backer_id)
            ->group_by('campaign_id')
            ->count('DISTINCT campaign_id');

        return $campaigns;
    }

    /**
     * Get the number of successfully backed campaign by a backer.
     * And the pledge status is BACKED or COMPLETED.
     * Also we need to make sure the payment status is PAID.
     *
     * @param int $backer_id
     * @return int
     */
    public function get_successfully_backed_campaigns_by_backer(int $backer_id)
    {
        $campaigns = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in('status', [PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->where('user_id', $backer_id)
            ->where('payment_status', PaymentStatus::PAID)
            ->group_by('campaign_id')
            ->count('DISTINCT campaign_id');

        return $campaigns;
    }

    /**
     * Get the total number of pledges for a campaign.
     * 
     * @param int $campaign_id
     * @param bool $is_only_pledges
     * @return int
     */
    public function get_total_number_of_pledges_for_campaign(int $campaign_id, bool $is_only_pledges = false)
    {
        $pledge_status = [
            PledgeStatus::PENDING,
            PledgeStatus::IN_PROGRESS,
            PledgeStatus::BACKED,
            PledgeStatus::COMPLETED,
            PledgeStatus::REFUNDED,
            PledgeStatus::FAILED,
            PledgeStatus::CANCELLED,
            PledgeStatus::TRASHED
        ];

        if ($is_only_pledges) {
            $pledge_status = [
                PledgeStatus::PENDING,
                PledgeStatus::IN_PROGRESS,
                PledgeStatus::BACKED,
                PledgeStatus::COMPLETED
            ];
        }

        $pledges = QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('campaign_id', $campaign_id)
            ->where_in('status', $pledge_status)
            ->count();

        return $pledges;
    }

    /**
     * Get the total number of uncharged pledges for a specific campaign.
     *
     * @param integer $campaign_id
     * @return int
     */
    public function get_total_number_of_uncharged_pledges_for_campaign(int $campaign_id)
    {
        return QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('campaign_id', $campaign_id)
            ->where_in('status', [PledgeStatus::PENDING])
            ->count();
    }

    /**
     * Check if a campaign is backed once or not.
     * If there is at least one pledge is backed or completed that means the campaign is backed once.
     *
     * @param integer $campaign_id
     * @return boolean
     */
    public function is_campaign_backed(int $campaign_id)
    {
        return QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('campaign_id', $campaign_id)
            ->where_in('status', [PledgeStatus::BACKED, PledgeStatus::COMPLETED])
            ->count() > 0;
    }

    /**
     * Get a pledge by ID.
     * @param int $id
     * @return PledgeDTO
     * @throws \Exception
     */
    public function get_by_id(int $id)
    {
        $result = $this->get_query(new PledgeFilterParamsDTO())->find($id, 'pledges.ID');

        if (!$result) {
            throw new Exception(esc_html__('Pledge not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->get_formatted_data($result);
    }

    /**
     * Get a pledge by uid.
     * @param string $uid
     * @return PledgeDTO
     * @throws \Exception
     */
    public function get_by_uid(string $uid)
    {
        $result = $this->get_query(new PledgeFilterParamsDTO())->find($uid, 'pledges.uid');

        if (!$result) {
            throw new Exception(esc_html__('Pledge not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->get_formatted_data($result);
    }

    /**
     * Get a pledge by transaction ID.
     * @param string $id
     * @return PledgeDTO
     * @throws \Exception
     */
    public function get_by_transaction_id(string $id)
    {
        $result = $this->get_query(new PledgeFilterParamsDTO())->where('transaction_id', $id)->first();

        if (!$result) {
            throw new Exception(esc_html__('Pledge not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->get_formatted_data($result);
    }

    /**
     * Create a new pledge.
     *
     * @param CreatePledgeDTO $dto The dto for the pledge.
     * @return int The ID of the newly created pledge.
     * @throws Exception If the data could not be inserted.
     */
    public function create(CreatePledgeDTO $dto)
    {
        $campaign_service = new CampaignService();
        $campaign = $campaign_service->get_by_id($dto->campaign_id);

        $this->check_campaign_constraints($campaign);

        $is_manual = !empty($dto->is_manual);

        if (growfund_user()->is_backer() && $is_manual) {
            throw new Exception(esc_html__('You can not create a manual pledge', 'growfund'), (int) Response::FORBIDDEN);
        }

        $reward = null;

        if ($dto->pledge_option === PledgeOption::WITH_REWARDS) {
            $reward_service = new RewardService();
            $reward = $reward_service->get_by_id($dto->reward_id);
            $this->check_reward_constraints($reward);
        } else {
            $this->check_amount_constraints((int) $dto->amount, $campaign);
        }

        if (empty($dto->user_info)) {
            $backer_dto = (new UserService())->get_by_user_id($dto->user_id);
			$backer_dto->image = $backer_dto->image['id'] ?? null;
			$user_info = PledgeBackerDTO::from_array($backer_dto->to_array());
			$dto->user_info = wp_json_encode($user_info->to_array());
        }

        if ($dto->reward_id) {
            $reward_dto = (new RewardService())->get_by_id($dto->reward_id);
            $reward_items = [];

            if (!empty($reward_dto->items)) {
                foreach ($reward_dto->items as $key => $item) {
                    $item->image = $item->image['id'] ?? null;
                    $reward_items[$key] = $item;
                }
            }

            $reward_info = PledgeRewardDTO::from_array([
                'id' => (string) $reward_dto->id,
                'title' => $reward_dto->title,
                'description' => $reward_dto->description,
                'image' => $reward_dto->image['id'] ?? null,
                'items' => $reward_items,
                'amount' => $reward_dto->amount ?? 0,
            ]);

            $dto->reward_info = wp_json_encode($reward_info->to_array());
        }

        $amount = PriceCalculator::calculate_pledge_amount($dto, $reward);
        $bonus_support_amount = PriceCalculator::calculate_pledge_bonus_amount($dto);

        $user_info = growfund_is_valid_json($dto->user_info) ? json_decode($dto->user_info, true) : [];
        $shipping_cost = PriceCalculator::calculate_shipping_cost($user_info['shipping_address'] ?? [], $reward);

        $dto->uid = growfund_uuid();
        $dto->is_manual = $is_manual;
        $dto->amount = $amount;
        $dto->bonus_support_amount = $bonus_support_amount;
        $dto->shipping_cost = $shipping_cost;
        $dto->created_at = $dto->created_at ?? Date::current_sql_safe();
        $dto->created_by = growfund_user()->get_id();
        $dto->updated_at = $dto->updated_at ?? Date::current_sql_safe();
        $dto->updated_by = growfund_user()->get_id();

        $dto->payment_status = $this->get_payment_status($dto->status, $is_manual);
        $dto->payment_method = wp_json_encode($dto->payment_method);
        $dto->payment_engine = growfund_payment_engine();

        $pledge_id = QueryBuilder::query()->table(Tables::PLEDGES)->create($dto->to_array());

        if ($pledge_id === false) {
            throw new Exception(esc_html__('Failed to create the pledge', 'growfund'));
        }

        growfund_event(new PledgeCreatedEvent($pledge_id, $dto, $campaign));

        if ($dto->status !== PledgeStatus::PENDING) {
            $pledge = $this->get_by_id($pledge_id);
            growfund_event(new PledgeStatusUpdateEvent($pledge, PledgeStatus::PENDING));
        }

        if ($campaign->status !== CampaignStatus::FUNDED && CampaignGoal::is_reached($campaign, $dto)) {
            $campaign_service->update_status($campaign->id, CampaignStatus::FUNDED);
            growfund_event(new GoalReachedEvent($campaign));
        }

        if (!$campaign->is_half_goal_achieved_already && CampaignGoal::is_half_goal_achieved($campaign, $dto)) {
            $campaign_service->marked_as_half_goal_achieved($campaign->id);
            growfund_event(new CampaignHalfMilestoneReachedEvent($campaign->id));
        }

        return $pledge_id;
    }

    /**
     * Determine the payment status for a pledge based on its status and whether it was made manually.
     * 
     * @param string $status The status of the pledge.
     * @param bool $is_manual Whether the pledge was made manually.
     * @return string The payment status.
     */
    protected function get_payment_status($status, $is_manual)
    {
        if (!$is_manual) {
            return PaymentStatus::PENDING;
        }

        if ($status === PledgeStatus::COMPLETED || $status === PledgeStatus::BACKED) {
            return PaymentStatus::PAID;
        } elseif ($status === PledgeStatus::FAILED || $status === PledgeStatus::CANCELLED) {
            return PaymentStatus::FAILED;
        }

        return PaymentStatus::UNPAID;
    }

    /**
     * Check campaign constraints for donation
     * @param CampaignDTO $campaign_dto
     * @param bool $throwable
     * @return bool
     * @throws ValidationException
     */
    public function check_campaign_constraints(CampaignDTO $campaign_dto, $throwable = true)
    {
        if (!$campaign_dto->is_launched) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('The campaign is not launched yet', 'growfund')]
            ]);
        }

        if ($campaign_dto->is_ended) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('Ended campaigns are not allowed to take pledges.', 'growfund')]
            ]);
        }

        if (
            $campaign_dto->reaching_action === ReachingAction::CLOSE
            && CampaignGoal::is_reached($campaign_dto)
        ) {
            if (!$throwable) {
                return false;
            }

            throw ValidationException::with_errors([
                'campaign_id' => [esc_html__('The campaign gaol already reached', 'growfund')]
            ]);
        }

        return true;
    }

    /**
     * Check amount constraints for pledge
     * @param int $pledge_amount
     * @param CampaignDTO $campaign_dto
     * @param bool $throwable
     * @return bool
     * @throws ValidationException
     */
    public function check_amount_constraints(int $pledge_amount, CampaignDTO $campaign_dto, $throwable = true) {
        if ($campaign_dto->appreciation_type === AppreciationType::GIVING_THANKS) {
            $min_amount = $campaign_dto->giving_thanks[0]['pledge_from'] ?? 0;
            $max_amount = $campaign_dto->giving_thanks[0]['pledge_to'] ?? 0;

			foreach ($campaign_dto->giving_thanks ?? [] as $option) {
				if ($option['pledge_from'] <= $min_amount) {
					$min_amount = $option['pledge_from'];
				}

				if ($option['pledge_to'] >= $max_amount) {
					$max_amount = $option['pledge_to'];
				}
			}

			if ($pledge_amount < (int) $min_amount) {
                if (!$throwable) {
                    return false;
                }

				throw ValidationException::with_errors([
					/* translators: %s: min pledge amount */
					'amount' => [sprintf(esc_html__('Minimum pledge amount is %s', 'growfund'), esc_html(Currency::format(Money::prepare_for_display($min_amount))))],
				]);
			}

			if ($pledge_amount > (int) $max_amount) {
                if (!$throwable) {
                    return false;
                }

				throw ValidationException::with_errors([
					/* translators: %s: max pledge amount */
					'amount' => [sprintf(esc_html__('Cannot pledge more than %s', 'growfund'), esc_html(Currency::format(Money::prepare_for_display($max_amount))))],
				]);
			}

            return true;
		}

		if (empty($campaign_dto->allow_pledge_without_reward)) {
            if (!$throwable) {
                return false;
			}
                
			throw ValidationException::with_errors([
				'reward_id' => [esc_html__('The campaign does not allow pledging without reward', 'growfund')],
			]);
		}

		if ($pledge_amount < (int) $campaign_dto->min_pledge_amount) {
            if (!$throwable) {
                return false;
			}
                
			throw ValidationException::with_errors([
				/* translators: %s: min pledge amount */
				'amount' => [sprintf(esc_html__('Minimum pledge amount is %s', 'growfund'), esc_html(Currency::format(Money::prepare_for_display($campaign_dto->min_pledge_amount))))],
			]);
		}

		if ($pledge_amount > (int) $campaign_dto->max_pledge_amount) {
            if (!$throwable) {
                return false;
			}
                
			throw ValidationException::with_errors([
				/* translators: %s: max pledge amount */
				'amount' => [sprintf(esc_html__('Cannot pledge more than %s', 'growfund'), esc_html(Currency::format(Money::prepare_for_display($campaign_dto->max_pledge_amount))))],
			]);
		}

        return true;
    }

    /**
     * Check reward constraints for pledge
     * @param int $campaign_id
     * @param RewardDTO $reward_dto
     * @param bool $throwable
     * @return bool
     * @throws ValidationException
     */
    public function check_reward_constraints(RewardDTO $reward_dto, $throwable = true)
    {
        if ($reward_dto->quantity_type === QuantityType::LIMITED) {
            if ($reward_dto->reward_left === 0) {
                if (!$throwable) {
					return false;
				}
            
                throw ValidationException::with_errors([
                    'reward_id' => [esc_html__('Reward limit reached', 'growfund')]
                ]);
            }
        }

        if ($reward_dto->time_limit_type === TimeLimitType::SPECIFIC_DATE) {
            $is_reward_available = !empty($reward_dto->limit_start_date) ? Date::is_date_in_past_or_present($reward_dto->limit_start_date) : false;
            $is_reward_ended = !empty($reward_dto->limit_end_date) ? Date::is_date_in_past($reward_dto->limit_end_date) : true;

            if (!$is_reward_available) {
                if (!$throwable) {
					return false;
				}
            
                throw ValidationException::with_errors([
                    'reward_id' => [esc_html__('Reward is not yet available', 'growfund')]
                ]);
            }

            if ($is_reward_ended) {
                if (!$throwable) {
					return false;
				}

                throw ValidationException::with_errors([
                    'reward_id' => [esc_html__('Reward time expired', 'growfund')]
                ]);
            }
        }

        return true;
    }

    /**
     * Partial update a pledge.
     *
     * @param array $data An array of data to update.
     * @return bool True if successfully updated the pledge or false.
     * @throws Exception If the data could not be updated.
     */
    public function partial_update($id, array $data)
    {
        $pledge_dto = $this->get_by_id($id);

        $data['updated_at'] = Date::current_sql_safe();
        $data['updated_by'] = growfund_user()->get_id();

        $result = QueryBuilder::query()->table(Tables::PLEDGES)->where('ID', $id)->update($data);

        if ($result === false) {
            throw new Exception(esc_html__('Failed to update the pledge', 'growfund'));
        }

        if (isset($data['status'])) {
            growfund_event(new PledgeStatusUpdateEvent($pledge_dto, $data['status']));
        }

        return true;
    }

    /**
     * Delete a pledge by its ID.
     *
     * @param int $id The ID of the pledge to delete.
     * @return bool True if the pledge was successfully deleted, false otherwise.
     * @throws Exception If the pledge could not be deleted due to its status.
     */
    public function delete(int $id, $force = false)
    {
        $pledge = QueryBuilder::query()->table(Tables::PLEDGES)->find($id);

        if (!$pledge) {
            return false;
        }

        if ($force) {
            return QueryBuilder::query()->table(Tables::PLEDGES)->where('ID', $id)->delete();
        }

        return QueryBuilder::query()->table(Tables::PLEDGES)->where('ID', $id)->update(['status' => PledgeStatus::TRASHED]);
    }

    public function bulk_actions(BulkActionDTO $dto)
    {
        $result = [];

        switch ($dto->action) {
            case 'trash':
                $result = $this->bulk_delete($dto->ids, false);
                break;
            case 'delete':
                $result = $this->bulk_delete($dto->ids, true);
                break;
            case 'restore':
                $result = $this->bulk_restore($dto->ids);
                break;
        }

        return $result;
    }

    /**
     * Delete multiple existing pledges by their id's with associated metadata.
     *
     * @param array $ids The ID's of the pledges.
     * @param bool $force Force delete or trash
     * @return array Response array with success and failure messages.
     * @throws Exception If something went wrong.
     */
    public function bulk_delete(array $ids, bool $force = false)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->delete($id, $force);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' =>  $force
                            ? __('Pledge could not be deleted.', 'growfund')
                            : __('Pledge could not be trashed.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' =>  $force
                            ? __('Pledge has been deleted.', 'growfund')
                            : __('Pledge has been trashed.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Restore multiple trashed pledges by their ids.
     * Iterates through each id and attempts to restore the corresponding pledge.
     * Collects information on which pledges were successfully restored and which failed.
     *
     * @param array<int> $ids The ids of the pledges to be restored.
     * @return array Contains 'succeeded' and 'failed' arrays with id and message for each pledge.
     * @throws Exception If an error occurs during the restoration process.
     */
    public function bulk_restore(array $ids)
    {
        $succeeded = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                $result = $this->restore($id);

                if ($result === false) {
                    $failed[] = [
                        'id' => $id,
                        'message' => __('Pledge could not be restored.', 'growfund'),
                    ];
                } else {
                    $succeeded[] = [
                        'id' => $id,
                        'message' => __('Pledge has been restored.', 'growfund'),
                    ];
                }
            } catch (Exception $error) {
                $failed[] = [
                    'id' => $id,
                    'message' => $error->getMessage(),
                ];
            }
        }

        return [
            'succeeded' => $succeeded,
            'failed' => $failed,
        ];
    }

    /**
     * Restore a trashed pledge.
     *
     * @param int $id The ID of the pledge to restore.
     * @return bool True if the pledge was successfully restored, false otherwise.
     */
    public function restore(int $id): bool
    {
        return QueryBuilder::query()->table(Tables::PLEDGES)->where('ID', $id)->update(['status' => PledgeStatus::PENDING]);
    }

    /**
     * Empty the trash of pledge.
     *
     * This function deletes all pledge with the status "trashed".
     *
     * @param int|null $user_id The ID of the user to empty the trash for.
     * @return bool True if the trash was successfully emptied, false otherwise.
     */
    public function empty_trash($user_id = null)
    {
        if (!empty($user_id)) {
            return QueryBuilder::query()
                ->table(Tables::PLEDGES . ' as donations')
                ->left_join(WP::POSTS_TABLE . ' as campaigns', 'pledges.campaign_id', 'campaigns.ID')
                ->where('pledges.status', PledgeStatus::TRASHED)
                ->where('campaigns.post_author', $user_id)
                ->delete('pledges');
        }

        return QueryBuilder::query()->table(Tables::PLEDGES)->where('status', PledgeStatus::TRASHED)->delete();
    }

    /**
     * Get the latest pledges.
     * @param PledgeFilterParamsDTO $params
     * @return Array<PledgeDTO>
     */
    public function latest(PledgeFilterParamsDTO $params)
    {
        $results = [];

        $query = $this->get_query($params)->order_by('created_at', 'DESC');

        if ($params->limit) {
            $query->limit($params->limit);
        }

        $results = $query->get();

        $items = [];

        foreach ($results as $row) {
            $items[] = $this->get_formatted_data($row);
        }

        return $items;
    }


    /**
     * Gets the total fund raised by a campaign in a given date range.
     *
     * @param int|null $campaign_id The campaign ID. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return int The total fund raised in cents.
     */
    public function get_total_pledges_amount_for_campaign($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;
        $pledges_table = Tables::PLEDGES;

        $query = QueryBuilder::query()->table($pledges_table)
            ->where_in(
                'status',
                [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::BACKED, PledgeStatus::COMPLETED]
            );

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->sum('amount + bonus_support_amount + shipping_cost');
    }

    /**
     * Get the total number of backers for a campaign in a given date range.
     *
     * @param int|null $campaign_id The campaign ID.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     * @param int|null $exclude_user_id The user ID to exclude. Optional.
     *
     * @return int The total number of backers.
     */
    public function get_total_backers_count_for_campaign($campaign_id = null, $start_date = null, $end_date = null, $exclude_user_id = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $query = QueryBuilder::query()->table(Tables::PLEDGES)
            ->where_in('status', [PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS, PledgeStatus::COMPLETED, PledgeStatus::BACKED]);

        if (growfund_user()->is_fundraiser()) {
            $campaign_ids = growfund_get_all_campaign_ids_by_fundraiser();
            $query->where_in('campaign_id', $campaign_ids);
        }

        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if (!empty($exclude_user_id)) {
            $query->where('user_id', '!=', $exclude_user_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        $result = $query
            ->select([
                'COUNT(DISTINCT user_id) + COUNT(DISTINCT CASE WHEN user_id IS NULL THEN email END) + SUM(user_id IS NULL AND email IS NULL) as total_count'
			])
            ->get();

        return (int) ($result[0]->total_count ?? 0);
    }


    /**
     * Retrieves the total refunded amount for a specific campaign within a date range.
     *
     * @param int|null $campaign_id The ID of the campaign. Optional.
     * @param string|null $start_date The start date in 'Y-m-d' format. Optional.
     * @param string|null $end_date The end date in 'Y-m-d' format. Optional.
     *
     * @return int The total refunded amount in cents. Returns 0 if no refunds are found.
     */

    public function get_total_refund_amount_for_campaign($campaign_id = null, $start_date = null, $end_date = null)
    {
        $start_date = !empty($start_date) ? new DateTime($start_date) : null;
        $end_date = !empty($end_date) ? new DateTime($end_date) : null;

        $postmeta_table = WP::POST_META_TABLE;
        $pledges_table = Tables::PLEDGES;
        $campaign_status = Arr::make([CampaignStatus::PUBLISHED, CampaignStatus::FUNDED])
            ->map(function ($status) {
                return "'{$status}'";
            })->join();

        $query = QueryBuilder::query()->table($pledges_table . ' as pledges')
            ->join_raw(
                "{$postmeta_table} as campaign_status_meta",
                'INNER',
                sprintf(
                    "campaign_status_meta.post_id = pledges.campaign_id AND campaign_status_meta.meta_key = '%s' AND campaign_status_meta.meta_value IN (%s) ",
                    growfund_with_prefix('status'),
                    $campaign_status
                )
            )->where('status', PledgeStatus::REFUNDED);


        if (!empty($campaign_id)) {
            $query->where('campaign_id', $campaign_id);
        }

        if ($start_date !== null) {
            $query->where_date('created_at', '>=', $start_date->format(DateTimeFormats::DB_DATE));
        }

        if ($end_date !== null) {
            $query->where_date('created_at', '<=', $end_date->format(DateTimeFormats::DB_DATE));
        }

        return $query->sum('amount + bonus_support_amount');
    }

    /**
     * Update the status of a pledge.
     *
     * @param int $id The ID of the pledge.
     * @param string $status The action to update the status.
     * @return bool True if the status was updated, false otherwise.
     */
    public function update_status(int $id, string $status)
    {
        $pledge = $this->get_by_id($id);

        $data = [
            'status' => $status,
            'updated_at' => Date::current_sql_safe(),
            'updated_by' => growfund_user()->get_id()
        ];

        if (in_array($status, [PledgeStatus::PENDING, PledgeStatus::CANCELLED], true)) {
            $data['payment_status'] = PaymentStatus::UNPAID;
        } elseif (in_array($status, [PledgeStatus::BACKED, PledgeStatus::COMPLETED], true)) {
            $data['payment_status']  = PaymentStatus::PAID;
        } elseif ($status === PledgeStatus::FAILED) {
            $data['payment_status'] = PaymentStatus::FAILED;
        }

        $is_updated = QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('ID', $id)
            ->update($data);

        if (!empty($is_updated)) {
            growfund_event(new PledgeStatusUpdateEvent($pledge, $status));
        }

        return !empty($is_updated);
    }

    /**
     * Get the latest contribution for a backer
     *
     * @param integer $backer_id
     * @return PledgeDTO|null
     */
    public function get_latest_contribution(int $backer_id)
    {
        $pledge = $this->get_query(new PledgeFilterParamsDTO())
            ->where('user_id', $backer_id)
            ->where_in(
                'status',
                [PledgeStatus::COMPLETED, PledgeStatus::BACKED, PledgeStatus::PENDING, PledgeStatus::IN_PROGRESS]
            )
            ->order_by('created_at', 'DESC')
            ->first();

        return $pledge ? $this->get_formatted_data($pledge) : null;
    }

    /**
     * Get the total number of completed pledge by a backer.
     *
     * @param integer $backer_id
     * @return int
     */
    public function get_total_count_of_completed_pledges(int $backer_id)
    {
        $donations = QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('user_id', $backer_id)
            ->where_in('status', [PledgeStatus::COMPLETED, PledgeStatus::BACKED])
            ->count();

        return $donations ?? 0;
    }

    /**
     * Get the total received amount by a fundraiser
     * 
     * @param int $fundraiser_id
     * 
     * @return int
     */
    public function get_total_received_amount_by_fundraiser(int $fundraiser_id)
    {
        $campaign_collaborators_table = QueryBuilder::query()->table(Tables::CAMPAIGN_COLLABORATORS)->get_table_name();

        $received_amount = QueryBuilder::query()
            ->table(Tables::PLEDGES . ' as pledges')
            ->inner_join(WP::POSTS_TABLE . ' as campaigns', 'campaigns.ID', 'pledges.campaign_id')
            ->where_in(
                'pledges.status',
                [PledgeStatus::COMPLETED, PledgeStatus::BACKED]
            )
            ->where_raw(sprintf(
                "(campaigns.post_author = %s OR EXISTS ( SELECT 1 FROM `%s` AS collaborators WHERE collaborators.campaign_id = pledges.campaign_id AND collaborators.collaborator_id = %s))",
                $fundraiser_id,
                $campaign_collaborators_table,
                $fundraiser_id
            ))
            ->sum('amount + bonus_support_amount + shipping_cost');

        return $received_amount;
    }

    public function get_count_of_pledges_by_rewards(int $reward_id)
    {
        return QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('reward_id', $reward_id)
            ->where('status', '!=', PledgeStatus::TRASHED)
            ->count();
    }

    /**
     * Charge a single backer for a specific pledge ID.
     *
     * @param int $pledge_id
     * @return bool
     */
    public function charge_pledged_backer(int $pledge_id)
    {
        $pledge = $this->get_by_id($pledge_id);

        if ($pledge->status !== PledgeStatus::PENDING) {
            return false;
        }

        growfund_scheduler()
            ->resolve(ChargeBackerService::class)
            ->with(['pledge_id' => $pledge_id])
            ->group('growfund_charge_backers')
            ->schedule_charge_backers();

        $this->update_status($pledge_id, PledgeStatus::IN_PROGRESS);

        return true;
    }

    /**
     * Retry failed payment for a specific pledge.
     * We are rescheduling charging the backer.
     *
     * @param int $pledge_id
     * @return bool
     */
    public function retry_failed_payment(int $pledge_id)
    {
        $pledge = $this->get_by_id($pledge_id);

        if ($pledge->status !== PledgeStatus::FAILED || $pledge->payment->payment_method->type !== PaymentGatewayType::ONLINE) {
            return false;
        }

        growfund_scheduler()
            ->resolve(ChargeBackerService::class)
            ->with(['pledge_id' => $pledge_id])
            ->group('growfund_charge_backers')
            ->schedule_charge_backers();

        $this->update_status($pledge_id, PledgeStatus::IN_PROGRESS);

        return true;
    }

    /**
     * Charge the backer pledges of a campaign.
     *
     * @param integer $campaign_id
     * @return bool
     */
    public function charge_backers(int $campaign_id)
    {
        $pledges = QueryBuilder::query()
            ->select(['ID'])
            ->table(Tables::PLEDGES)
            ->where('campaign_id', $campaign_id)
            ->where('is_manual', 0)
            ->where('status', PledgeStatus::PENDING)
            ->get();

        if (empty($pledges)) {
            return false;
        }

        foreach ($pledges as $pledge) {
            growfund_scheduler()
                ->resolve(ChargeBackerService::class)
                ->with(['pledge_id' => $pledge->ID])
                ->group('growfund_charge_backers')
                ->schedule_charge_backers();
        }

        return $this->move_to_in_progress($campaign_id);
    }

    /**
     * Check if a user has contributed to a campaign.
     *
     * @param int $campaign_id
     * @param int $user_id
     * @return bool
     */
    public function has_contributions(int $campaign_id, int $user_id)
    {
        return QueryBuilder::query()
            ->table(Tables::PLEDGES)
            ->where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->where_in('status', [
                PledgeStatus::BACKED,
                PledgeStatus::COMPLETED
            ])
            ->where('payment_status', PaymentStatus::PAID)
            ->count();
    }

    /**
     * Move the pledges to in progress status.
     *
     * @param integer $campaign_id
     * @return bool
     */
    protected function move_to_in_progress(int $campaign_id)
    {
        try {
            QueryBuilder::query()
                ->table(Tables::PLEDGES)
                ->where('campaign_id', $campaign_id)
                ->where('status', PledgeStatus::PENDING)
                ->update([
                    'status' => PledgeStatus::IN_PROGRESS
                ]);
        } catch (Exception $error) {
            return false;
        }

        return true;
    }

    public function get_appreciation_message(CampaignDTO $campaign_dto, int $pledge_amount = 0)
    {
        $giving_thanks = $campaign_dto->giving_thanks ?? [];

        foreach ($giving_thanks as $option) {
            if ($pledge_amount >= $option['pledge_from'] && $pledge_amount <= $option['pledge_to']) {
                return $option['appreciation_message'];
            }
        }

        return '';
    }
}
