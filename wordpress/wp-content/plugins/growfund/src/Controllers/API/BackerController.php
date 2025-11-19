<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\UserDeleteType;
use Growfund\Contracts\Request;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\Backer\CreateBackerDTO;
use Growfund\DTO\Backer\UpdateBackerDTO;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\BackerPolicy;
use Growfund\Sanitizer;
use Growfund\Services\ActivityService;
use Growfund\Services\BackerService;
use Growfund\Services\CampaignService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Supports\Money;
use Growfund\Validation\Validator;
use Exception;
use Growfund\Constants\HookNames;

/**
 * Class BackerController
 * @since 1.0.0
 */
class BackerController
{
    /**
     * Backer service instance.
     *
     * @var BackerService
     */
    protected $service;

    /**
     * Campaign service instance.
     *
     * @var CampaignService
     */
    protected $campaign_service;

    /**
     * Pledge service instance.
     *
     * @var PledgeService
     */
    protected $pledge_service;

    protected $policy;


    /**
     * Initialize the controller with BackerService.
     */
    public function __construct(BackerService $service, CampaignService $campaign_service, PledgeService $pledge_service, BackerPolicy $backer_policy)
    {
        $this->service = $service;
        $this->campaign_service = $campaign_service;
        $this->pledge_service = $pledge_service;
        $this->policy = $backer_policy;
    }

    /**
     * Create new backer
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $this->policy->authorize_create();

        $data = $request->all();

        $validator = Validator::make($data, CreateBackerDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, CreateBackerDTO::sanitization_rules())->get_sanitized_data();

        $backer_dto = CreateBackerDTO::from_array($sanitized_data);

        $backer_id = $this->service->store($backer_dto);

        $response = [
            'data' => ['id' => (string) $backer_id],
            'message' => __('Backer created successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update backer
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $backer_id = $request->get_int('id');

        if (! growfund_user($backer_id)->is_backer()) {
            throw new Exception(esc_html__('Invalid backer ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_update($backer_id);

        $data = $request->all();

        $validator = Validator::make($data, UpdateBackerDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, UpdateBackerDTO::sanitization_rules())->get_sanitized_data();

        $backer_dto = UpdateBackerDTO::from_array($sanitized_data);

        $result = $this->service->update($backer_id, $backer_dto);

        $response = [
            'data' => $result,
            'message' => __('Backer updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /** 
     * Return a paginated list of backers.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function paginated(Request $request)
    {
        $this->policy->authorize_paginated();

        return growfund_response()->json([
            'data' => $this->service->paginated([
                'page' => $request->get_int('page', 1),
                'limit' => $request->get_int('per_page', 10),
                'orderby' => $request->get_int('orderby', 'ID'),
                'order' => $request->get_int('order', 'DESC'),
                'campaign_id' => $request->get_string('campaign_id', null),
                'search' => $request->get_string('search'),
                'date_from' => $request->get_date('start_date'),
                'date_to' => $request->get_date('end_date'),
                'status' => $request->get_string('status'),
            ]),
            'message' => '',
        ]);
    }

    /**
     * Get all the campaigns contributed by a backer.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function campaigns_by_backer(Request $request)
    {
        $backer_id = $request->get_int('backer_id');

        if (! growfund_user($backer_id)->is_backer()) {
            throw new Exception(esc_html__('Invalid backer ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_campaigns_by_backer($backer_id);

        $filter_dto = new CampaignFiltersDTO();
        $filter_dto->page = $request->get_int('page', 1);
        $filter_dto->limit = $request->get_int('limit', 10);
        $filter_dto->search = $request->get_string('search');
        $filter_dto->status = $request->get_string('status');
        $filter_dto->start_date = $request->get_date('start_date');
        $filter_dto->end_date = $request->get_date('end_date');

        $campaign_data = $this->service->get_campaign_ids_by_backer($backer_id);
        $filter_dto->post_ids = array_keys($campaign_data);

        if (empty($filter_dto->post_ids)) {
            $filter_dto->post_ids = [0];
        }

        $campaigns = $this->campaign_service->paginated($filter_dto);

        foreach ($campaigns->results as &$campaign) {
            $campaign->number_of_individual_contributions = intval($campaign_data[$campaign->id] ?? 0);
        }

        unset($campaign);

        return growfund_response()->json([
            'data' => $campaigns,
            'message' => '',
        ]);
    }

    /**
     * Return response with backer overview
     * 
     * @param Request $request
     * @return Response
     */
    public function overview(Request $request)
    {
        $backer_id = $request->get_int('id');

        if (! growfund_user($backer_id)->is_backer()) {
            throw new Exception(esc_html__('Invalid backer ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_overview($backer_id);

        $data = $this->service->get_overview($backer_id);

        return growfund_response()->json([
            'data' => apply_filters(HookNames::GROWFUND_BACKER_OVERVIEW_FILTER, $data, $backer_id),
            'message' => '',
        ]);
    }

    /**
     * Delete backer
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $id = $request->get_int('id');

        if (! growfund_user($id)->is_backer()) {
            throw new Exception(esc_html__('Invalid backer ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_delete($id);

        $delete_type = growfund_user()->get_id() === $id ? UserDeleteType::ANONYMIZE : UserDeleteType::TRASH;

        $is_deleted = $this->service->delete($id, $delete_type);

        $response = [
            'data' => $is_deleted,
            'message' => __('Backer deleted successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Empty soft backer
     *
     * @return Response
     */
    public function empty_trash(Request $request)
    {
        $this->policy->authorize_empty_trash();

        $is_deleted = $this->service->empty_trash($request->get_bool('is_permanent_delete'));

        if (!$is_deleted) {
            return growfund_response()->json([
                'data' => $is_deleted,
                'message' => __('Failed to empty trash', 'growfund'),
            ], Response::INTERNAL_SERVER_ERROR);
        }

        return growfund_response()->json([
            'data' => $is_deleted,
            'message' => __('Trash emptied successfully', 'growfund'),
        ]);
    }

    /**
     * Handle bulk actions
     * 
     * @param \Growfund\Contracts\Request $request
     * @return Response
     */
    public function bulk_actions(Request $request)
    {
        $this->policy->authorize_bulk_actions();

        $validator = Validator::make($request->all(), [
            'ids'       => 'required|array',
            'action'    => 'required|string|in:trash,delete,restore',
            'is_permanent_delete' => 'required_if:action,delete|boolean',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $result = [];

        switch ($request->get_string('action')) {
            case 'trash':
                $result = $this->service->bulk_delete($request->get_array('ids'), UserDeleteType::TRASH);
                break;
            case 'delete':
                $type = $request->get_bool('is_permanent_delete', false) ? UserDeleteType::PERMANENT : UserDeleteType::ANONYMIZE;
                $result = $this->service->bulk_delete($request->get_array('ids'), $type);
                break;
            case 'restore':
                $result = $this->service->bulk_restore($request->get_array('ids'));
                break;
        }


        $failed = empty($result['failed']) ? [] : Arr::make($result['failed'])->pluck('id')->toArray();

        $message = empty($failed)
            ?  __('Bulk action successfully applied for all the selected backer.', 'growfund')
            : sprintf(
                /* translators: %s: backer ids */
                __('Bulk action successfully applied for all the selected backer except the backer with id: %s.', 'growfund'),
                implode(', ', $failed)
            );

        return growfund_response()->json([
            'data' => $result,
            'message' => $message,
        ], Response::MULTI_STATUS);
    }

    /**
     * Calculate the giving stats for a backer.
     *
     * @param Request $request
     * @return Response
     */
    public function giving_stats(Request $request)
    {
        $backer_id = $request->get_int('backer_id');

        $this->policy->authorize_giving_stats($backer_id);

        $stats = [
            'pledged_amount' => Money::prepare_for_display(
                $this->pledge_service->get_total_pledges_amount($backer_id)
            ),
            'total_pledges' => $this->pledge_service->get_total_number_of_pledges(
                $backer_id
            ),
            'backed_amount' => Money::prepare_for_display(
                $this->pledge_service->get_total_backed_amount($backer_id)
            ),
            'backed_campaigns' => $this->pledge_service->get_successfully_backed_campaigns_by_backer(
                $backer_id
            ),
        ];

        return growfund_response()->json([
            'data' => $stats,
            'message' => '',
        ]);
    }

    public function activities(Request $request)
    {
        $backer_id = $request->get_int('id');

        if (! growfund_user($backer_id)->is_backer()) {
            throw new Exception(esc_html__('Invalid backer ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $activity_filter_dto = ActivityFilterDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'orderby' => $request->get_string('orderby', 'created_at'),
            'order' => $request->get_string('order', 'DESC'),
            'user_id' => $backer_id,
        ]);

        $activities = (new ActivityService())->paginated($activity_filter_dto, Activities::BACKER);

        return growfund_response()->json([
            'data' => $activities,
            'message' => '',
        ]);
    }
}
