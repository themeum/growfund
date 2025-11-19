<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Contracts\Request;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\BulkActionDTO;
use Growfund\DTO\Donation\CreateDonationDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\Http\Response;
use Growfund\Exceptions\ValidationException;
use Growfund\Policies\DonationPolicy;
use Growfund\Sanitizer;
use Growfund\Services\ActivityService;
use Growfund\Services\DonationService;
use Growfund\Supports\Option;
use Growfund\Supports\Arr;
use Growfund\Supports\Payment;
use Growfund\Validation\Validator;

/**
 * Class DonationController
 *
 * @since 1.0.0
 */
class DonationController
{
    /**
     * DonationService instance.
     *
     * @var DonationService
     */
    protected $service;
    protected $policy;

    /**
     * Initialize the controller with DonationService.
     */
    public function __construct(DonationService $service, DonationPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Get donations paginated data
     *
     * @param \Growfund\Contracts\Request $request
     * @return Response
     */
    public function paginated(Request $request)
    {
        $this->policy->authorize_paginated();

        $dto = new DonationFilterParamsDTO();

        $dto->page = $request->get_int('page', 1);
        $dto->limit = $request->get_int('per_page', 10);
        $dto->search = $request->get_string('search');
        $dto->campaign_id = $request->get_int('campaign_id');
        $dto->fund_id = $request->get_int('fund_id');
        $dto->status = $request->get_string('status');
        $dto->start_date = $request->get_date('start_date');
        $dto->end_date = $request->get_date('end_date');
        $dto->user_id = $request->get_int('user_id');
        $dto->orderby = $request->get_string('orderby', 'created_at');
        $dto->order = $request->get_string('order', 'DESC');

        if (growfund_user()->is_donor()) {
            $dto->user_id = growfund_user()->get_id();
        }

        $donations = $this->service->paginated($dto);

        return growfund_response()->json([
            'data' => $donations,
            'message' => '',
        ]);
    }

    /**
     * Retrieve a donation by ID.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function get_by_id(Request $request)
    {
        $this->policy->authorize_get_by_id($request->get_int('id'));

        $data = $this->service->get_by_id($request->get_int('id'));

        return growfund_response()->json([
            'data' => $data,
            'message' => '',
        ]);
    }

    /**
     * Create a new donation.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, CreateDonationDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_create($request->get_int('campaign_id'));

        $sanitized_data = Sanitizer::make($data, CreateDonationDTO::sanitization_rules())->get_sanitized_data();
        $dto = CreateDonationDTO::from_array($sanitized_data);
        $dto->is_manual = true;
        $dto->payment_method = !empty($sanitized_data['payment_method']) ? Payment::get_payment_method_by_name($sanitized_data['payment_method']) :  null;

        $id = $this->service->create($dto);

        $response = [
            'data' => ['id' => (string) $id],
            'message' => __('Donation created successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update the status of a donation.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function update_status(Request $request)
    {
        $statuses = [
            DonationStatus::PENDING,
            DonationStatus::COMPLETED,
            DonationStatus::FAILED,
            DonationStatus::CANCELLED,
            DonationStatus::REFUNDED,
        ];

        if (growfund_user()->is_donor()) {
            $statuses = [DonationStatus::CANCELLED];
        }

        $validator = Validator::make($request->all(), [
            'id'       => 'required',
            'action'   => 'required|string|in:' . implode(',', $statuses),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update_status($request->get_int('id'), growfund_user()->get_id());

        $is_updated = $this->service->update_status(
            $request->get_int('id'),
            $request->get_string('action')
        );

        $response = [
            'data' => $is_updated,
            'message' => __('Donation status updated successfully', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    /**
     * Delete a donation.
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
            'message' => $result ? __('Donation deleted successfully.', 'growfund') : __('Failed to delete donation.', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    /**
     * Empty trash donations
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
            'action'    => 'required|string|in:trash,restore,delete,reassign_fund',
            'fund_id'   => 'required_if:action,reassign_fund',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $dto = BulkActionDTO::from_array_with_sanitize($request->all());
        $dto->meta['fund_id'] = $request->get_int('fund_id');

        $this->policy->authorize_bulk_actions($request->get_string('action'), $request->get_array('ids'));

        $result = $this->service->bulk_actions($dto);

        $failed = empty($result['failed']) ? [] : Arr::make($result['failed'])->pluck('id')->toArray();

        $message = empty($failed)
            ? __('Bulk action successfully applied for all the selected donations.', 'growfund')
            : sprintf(
                /* translators: %s: donation ids */
                __('Bulk action successfully applied for all the selected donations except the donations with id: %s.', 'growfund'),
                implode(', ', $failed)
            );

        return growfund_response()->json([
            'data' => $result,
            'message' => $message,
        ], Response::MULTI_STATUS);
    }

    public function activities(Request $request)
    {
        $donation_id = $request->get_int('id');

        $activity_filter_dto = ActivityFilterDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'orderby' => $request->get_string('orderby', 'created_at'),
            'order' => $request->get_string('order', 'DESC'),
            'donation_id' => $donation_id,
        ]);

        $activities = (new ActivityService())->paginated($activity_filter_dto, Activities::DONATION);

        return growfund_response()->json([
            'data' => $activities,
            'message' => '',
        ]);
    }

    public function get_receipt(Request $request)
    {
        $uid = $request->get_string('uid');

        return growfund_response()->json([
            'data' => [
                'donation' => $this->service->get_by_uid($uid),
                'template' => Option::get(OptionKeys::PDF_DONATION_RECEIPT_TEMPLATE)
            ],
            'message' => '',
        ]);
    }

    public function get_ecard(Request $request)
    {
        $uid = $request->get_string('uid');

        return growfund_response()->json([
            'data' => [
                'donation' => $this->service->get_by_uid($uid),
                'template' => Option::get(OptionKeys::ECARD_TEMPLATE)
            ],
            'message' => '',
        ]);
    }
}
