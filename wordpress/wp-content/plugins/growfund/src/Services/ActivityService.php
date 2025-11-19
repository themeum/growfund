<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\ActivityType;
use Growfund\Constants\MetaKeys\Campaign;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\Activity\ActivityResponseDTO;
use Growfund\Http\Response;
use Growfund\QueryBuilder;
use Growfund\Supports\Date;
use Growfund\Supports\MediaAttachment;
use Exception;

class ActivityService
{
    /**
     * @param \Growfund\DTO\Activity\ActivityFilterDTO $dto
     * @param string $activity_cause
     */
    public function paginated(ActivityFilterDTO $dto, $activity_cause)
    {
        $query = $this->get_query();

        if (!empty($dto->campaign_id)) {
            $query->where('activities.campaign_id', $dto->campaign_id);
        }

        if (!empty($dto->pledge_id)) {
            $query->where('activities.pledge_id', $dto->pledge_id);
        }

        if (!empty($dto->donation_id)) {
            $query->where('activities.donation_id', $dto->donation_id);
        }

        if (!empty($dto->user_id)) {
            if ($activity_cause === Activities::FUNDRAISER) {
                $campaign_collaborators_table = QueryBuilder::query()
                    ->table(Tables::CAMPAIGN_COLLABORATORS)->get_table_name();

                $query->where_raw(sprintf(
                    "(campaigns.post_author = %s OR EXISTS ( SELECT 1 FROM `%s` AS collaborators WHERE collaborators.campaign_id = activities.campaign_id AND collaborators.collaborator_id = %s))",
                    $dto->user_id,
                    $campaign_collaborators_table,
                    $dto->user_id
                ));
            } else {
                $query->where_raw(sprintf("(activities.created_by = %s OR activities.user_id = %s)", $dto->user_id, $dto->user_id));
            }
        }

        $paginated = $query->where_in('activities.type', $this->get_activity_types($activity_cause))
            ->order_by($dto->orderby ?? 'created_at', !empty($dto->order) ? strtoupper($dto->order) : 'DESC')
            ->paginate($dto->page, $dto->limit);

        $is_donation_mode = growfund_app()->is_donation_mode();

        foreach ($paginated['results'] as $key => $result) {
            $paginated['results'][$key] = $this->prepare_activity_dto($result, $is_donation_mode);
        }

        return $paginated;
    }

    /**
     * @param \Growfund\Constants\Activities $activity_cause
     * 
     * @return array 
     */
    protected function get_activity_types($activity_cause)
    {
        switch ($activity_cause) {
            case Activities::PLEDGE:
                return [
                    ActivityType::TIMELINE,
                    ActivityType::PLEDGE_CREATION,
                    ActivityType::PLEDGE_CANCELLED,
                    ActivityType::PLEDGE_BACKED,
                    ActivityType::PLEDGE_FAILED_TO_BACK,
                    ActivityType::PLEDGE_COMPLETED,
                    ActivityType::PLEDGE_REFUND_RECEIVED,
                ];
            case Activities::BACKER:
                return [
                    ActivityType::PLEDGE_CREATION,
                    ActivityType::PLEDGE_CANCELLED,
                    ActivityType::PLEDGE_BACKED,
                    ActivityType::PLEDGE_FAILED_TO_BACK,
                    ActivityType::PLEDGE_REFUND_REQUESTED,
                    ActivityType::PLEDGE_REFUND_RECEIVED,
                    ActivityType::CAMPAIGN_COMMENT
                ];
            case Activities::DONATION:
                return [
                    ActivityType::TIMELINE,
                    ActivityType::DONATION_CREATION,
                    ActivityType::DONATION_CANCELLED,
                    ActivityType::DONATION_FAILED,
                    ActivityType::DONATION_COMPLETED,
                    ActivityType::DONATION_REFUND_RECEIVED,
                ];
            case Activities::DONOR:
                return [
                    ActivityType::DONATION_CANCELLED,
                    ActivityType::DONATION_FAILED,
                    ActivityType::DONATION_COMPLETED,
                    ActivityType::DONATION_REFUND_REQUESTED,
                    ActivityType::DONATION_REFUND_RECEIVED,
                    ActivityType::CAMPAIGN_COMMENT,
                ];
            case Activities::FUNDRAISER:
                return [
                    ActivityType::CAMPAIGN_SUBMITTED_FOR_REVIEW,
                    ActivityType::CAMPAIGN_RE_SUBMITTED_FOR_REVIEW,
                    ActivityType::CAMPAIGN_DECLINED,
                    ActivityType::CAMPAIGN_APPROVED_AND_PUBLISHED,
                    ActivityType::CAMPAIGN_SET_DEADLINE,
                    ActivityType::CAMPAIGN_REMOVED_DEADLINE,
                    ActivityType::CAMPAIGN_EXTENDED_DEADLINE,
                    ActivityType::CAMPAIGN_MARKED_AS_COMPLETED,
                    ActivityType::CAMPAIGN_POST_UPDATE,
                    ActivityType::CAMPAIGN_GOAL_REACHED,
                ];
            default:
                return [];
        }
    }

    public function save(ActivityDTO $activity_dto)
    {
        if (empty($activity_dto->created_by)) {
            $user_id = growfund_user()->get_id();
            $activity_dto->created_by = $user_id ? $user_id : null;
        }

        $activity_dto->created_at = Date::current_sql_safe();

        $activity_id = QueryBuilder::query()
            ->table(Tables::ACTIVITIES)
            ->create($activity_dto->except(['id']));

        if (empty($activity_id)) {
            throw new Exception(esc_html__('Failed to create the activity', 'growfund'));
        }

        return $activity_id;
    }


    /**
     * Get query
     * @return QueryBuilder
     */
    protected function get_query()
    {
        $activities_table = Tables::ACTIVITIES;
        $campaigns_table = WP::POSTS_TABLE;
        $campaigns_meta_table = WP::POST_META_TABLE;
        $users_table = WP::USERS_TABLE;
        $users_meta_table = WP::USER_META_TABLE;

        $query =  QueryBuilder::query()
            ->table($activities_table . ' as activities')
            ->left_join($campaigns_table . ' as campaigns', 'campaigns.ID', 'activities.campaign_id')
            ->join_raw("{$campaigns_meta_table} as campaign_images_meta", "LEFT", sprintf("campaigns.ID = campaign_images_meta.post_id AND campaign_images_meta.meta_key = '%s'", growfund_with_prefix(Campaign::IMAGES)))
            ->left_join($users_table . ' as users', 'users.ID', 'activities.created_by')
            ->join_raw("{$users_meta_table} as users_image_meta", "LEFT", sprintf("users.ID = users_image_meta.user_id AND users_image_meta.meta_key = '%s'", growfund_with_prefix('image')))
            ->select([
                'activities.*',
                'campaigns.post_title as campaign_title',
                'users.display_name as created_by_name',
            ]);

        return $query;
    }

    public function get_by_id(int $id)
    {
        $activity = $this->get_query()->find($id, 'activities.ID');

        if (empty($activity)) {
            throw new Exception(esc_html__('Activity not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        return $this->prepare_activity_dto($activity, growfund_app()->is_donation_mode());
    }

    protected function prepare_activity_dto($activity, bool $is_donation_mode)
    {
        $activity_dto = new ActivityResponseDTO();

        $is_timeline = $activity->type === ActivityType::TIMELINE;

        $activity_dto->id = (string) $activity->ID;
        $activity_dto->type = $activity->type;
        $activity_dto->campaign_id = (string) $activity->campaign_id;

        if (!$is_timeline) {
            $activity_dto->campaign_title = $activity->campaign_title;
            $activity_dto->campaign_images = !empty($activity->campaign_images) ? MediaAttachment::make_many(maybe_unserialize($activity->campaign_images)) : [];
        }

        if (!$is_donation_mode) {
            $activity_dto->pledge_id = (string) $activity->pledge_id;
        }

        if ($is_donation_mode) {
            $activity_dto->donation_id = (string) $activity->donation_id;
        }

        $activity_dto->user_id = (string) $activity->user_id;
        $activity_dto->created_by = (string) $activity->created_by;
        $activity_dto->created_by_name = $activity->created_by_name;
        $activity_dto->created_by_image = !empty($activity->created_by_image) ? MediaAttachment::make($activity->created_by_image) : null;
        $activity_dto->created_at = Date::human_readable_time_diff($activity->created_at);

        $activity_dto->data = !empty($activity->data) ? json_decode($activity->data, true) : null;


        return $activity_dto;
    }

    public function delete_by_id(int $id)
    {
        $deleted = QueryBuilder::query()
            ->table(Tables::ACTIVITIES)
            ->where('ID', $id)
            ->delete();

        return !empty($deleted);
    }
}
