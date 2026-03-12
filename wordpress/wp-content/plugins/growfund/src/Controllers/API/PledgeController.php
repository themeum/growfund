<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Contracts\Request;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\BulkActionDTO;
use Growfund\DTO\Pledge\PledgeFilterParamsDTO;
use Growfund\Http\Response;
use Growfund\Services\PledgeService;
use Growfund\DTO\Pledge\CreatePledgeDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Policies\PledgePolicy;
use Growfund\Sanitizer;
use Growfund\Services\ActivityService;
use Growfund\Supports\Option;
use Growfund\Supports\Arr;
use Growfund\Supports\Payment;
use Growfund\Validation\Validator;
use Exception;

/**
 * Class PledgeController
 *
 * @since 1.0.0
 */
class PledgeController
{
    /**
     * PledgeService instance.
     *
     * @var PledgeService
     */
    protected $service;
    protected $policy;

    /**
     * Initialize the controller with PledgeService.
     */
    public function __construct(PledgeService $service, PledgePolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Get pledges paginated data
     * @param \Growfund\Http\Request $request
     * 
     * @return \Growfund\Http\Response
     */
    public function paginated(Request $request)
    {
        $this->policy->authorize_paginated();

        $params = PledgeFilterParamsDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'search' => $request->get_string('search'),
            'campaign_id' => $request->get_int('campaign_id'),
            'user_id' => $request->get_int('user_id'),
            'status' => $request->get_string('status'),
            'start_date' => $request->get_date('start_date'),
            'end_date' => $request->get_date('end_date'),
        ]);

        if (growfund_user()->is_backer()) {
            $params->user_id = growfund_user()->get_id();
        }

        $pledges = $this->service->paginated($params);

        return growfund_response()->json([
            'data' => $pledges,
            'message' => '',
        ]);
    }

    /**
     * Get pledges paginated data by backer id
     * @param \Growfund\Http\Request $request
     * 
     * @return \Growfund\Http\Response
     */
    public function backer_pledges(Request $request)
    {
        $this->policy->authorize_backer_pledges($request->get_int('backer_id'));

        $backer = get_userdata($request->get_int('backer_id'));

        if (!$backer || !in_array(Backer::ROLE, $backer->roles, true)) {
            throw new Exception(esc_html__('Backer not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $params = PledgeFilterParamsDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'search' => $request->get_string('search'),
            'status' => $request->get_string('status'),
            'start_date' => $request->get_date('start_date'),
            'end_date' => $request->get_date('end_date'),
            'user_id' => $backer->ID,
        ]);

        $pledges = $this->service->paginated($params);

        return growfund_response()->json([
            'data' => $pledges,
            'message' => '',
        ]);
    }

    /**
     * Retrieve a pledge by ID.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function get_by_id(Request $request)
    {
        $this->policy->authorize_get_by_id($request->get_int('id'));

        $data = $this->service->get_by_id($request->get_int("id"));

        return growfund_response()->json([
            'data' => $data,
            'message' => '',
        ]);
    }

    /**
     * Create a new pledge.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, CreatePledgeDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_create($request->get_int('campaign_id'));

        $sanitized_data = Sanitizer::make($data, CreatePledgeDTO::sanitization_rules())->get_sanitized_data();
        $pledge_dto = CreatePledgeDTO::from_array($sanitized_data);
        $pledge_dto->payment_method = !empty($sanitized_data['payment_method'])
            ? Payment::get_payment_method_by_name($sanitized_data['payment_method'])
            :  null;
        $pledge_dto->is_manual = true;

        $id = $this->service->create($pledge_dto);

        $response = [
            'data' => ['id' => (string) $id],
            'message' => __('Pledge created successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update the status of a pledge.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function update_status(Request $request)
    {
        $statuses = [
            PledgeStatus::PENDING,
            PledgeStatus::COMPLETED,
            PledgeStatus::BACKED,
            PledgeStatus::FAILED,
            PledgeStatus::CANCELLED,
            PledgeStatus::REFUNDED,
        ];

        if (growfund_user()->is_backer()) {
            $statuses = [PledgeStatus::CANCELLED];
        }

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'action'   => 'required|string|in:' . implode(',', $statuses),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update_status($request->get_int('id'));

        $is_updated = $this->service->update_status(
            $request->get_int('id'),
            $request->get_string('action')
        );

        return growfund_response()->json([
            'data' => $is_updated,
            'message' => __('Pledge status updated successfully', 'growfund'),
        ]);
    }

    /**
     * Delete a pledge.
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $id = $request->get_int('id');
        $is_permanent = $request->get_bool('is_permanent', false);

        $this->policy->authorize_delete($id);

        $result = $this->service->delete($id, $is_permanent);

        $response = [
            'data' => $result,
            'message' => $result ? __('Pledge deleted successfully.', 'growfund') : __('Failed to delete pledge.', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    /**
     * Empty trash pledges
     *
     * @return Response
     */
    public function empty_trash()
    {
        $this->policy->authorize_empty_trash();

        $is_deleted = $this->service->empty_trash(growfund_user()->get_id());

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
        $validator = Validator::make($request->all(), [
            'ids'       => 'required|array',
            'action'    => 'required|string|in:trash,restore,delete',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $dto = BulkActionDTO::from_array_with_sanitize($request->all());

        $this->policy->authorize_bulk_actions($request->get_array('ids'));

        $result = $this->service->bulk_actions($dto);

        $failed = empty($result['failed']) ? [] : Arr::make($result['failed'])->pluck('id')->toArray();

        $message = empty($failed)
            ?  __('Bulk action successfully applied for all the selected pledges.', 'growfund')
            : sprintf(
                /* translators: %s: pledge ids */
                __('Bulk action successfully applied for all the selected pledges except the pledges with id: %s.', 'growfund'),
                implode(', ', $failed)
            );

        return growfund_response()->json([
            'data' => $result,
            'message' => $message,
        ], Response::MULTI_STATUS);
    }

    public function activities(Request $request)
    {
        $pledge_id = $request->get_int('id');

        $activity_filter_dto = ActivityFilterDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'orderby' => $request->get_column('orderby', 'created_at', ['ID', 'type', 'created_at']),
            'order' => $request->get_string('order', 'DESC'),
            'pledge_id' => $pledge_id,
        ]);

        $activities = (new ActivityService())->paginated($activity_filter_dto, Activities::PLEDGE);

        return growfund_response()->json([
            'data' => $activities,
            'message' => '',
        ]);
    }

    /**
     * Charge a backer for a specific pledge
     *
     * @param Request $request
     * @return Response
     */
    public function charge_backer(Request $request)
    {
        $pledge_id = $request->get_int('id');

        $is_charged = $this->service->charge_pledged_backer($pledge_id);

        return growfund_response()->json([
            'data' => $is_charged,
            'message' => $is_charged ? __('Backer charged successfully', 'growfund') : __('Failed to charge backer', 'growfund'),
        ]);
    }

    /**
     * Retry failed payment for a specific pledge
     *
     * @param Request $request
     * @return Response
     */
    public function retry_failed_payment(Request $request)
    {
        $pledge_id = $request->get_int('id');
        $is_retried = $this->service->retry_failed_payment($pledge_id);

        return growfund_response()->json([
            'data' => $is_retried,
            'message' => $is_retried ? __('Failed payment retried successfully', 'growfund') : __('Failed to retry failed payment', 'growfund'),
        ]);
    }

    public function ready_for_pickup(Request $request)
    {
        $this->policy->authorize_mark_as_ready_for_pickup($request->get_int('id'));

        $pledge_id = $request->get_int('id');
        $is_ready = $this->service->marked_as_ready_for_pickup($pledge_id);

        return growfund_response()->json([
            'data' => $is_ready,
            'message' => $is_ready ? __('Reward is marked as ready for pickup', 'growfund') : __('Failed to mark as ready for pickup', 'growfund'),
        ]);
    }

    public function get_receipt(Request $request)
    {
        $uid = $request->get_string('uid');

        return growfund_response()->json([
            'data' => [
                'pledge' => $this->service->get_by_uid($uid),
                'template' => Option::get(OptionKeys::PDF_PLEDGE_RECEIPT_TEMPLATE)
            ],
            'message' => '',
        ]);
    }
}
