<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\Pagination;
use Growfund\Constants\UserDeleteType;
use Growfund\Contracts\Request;
use Growfund\DTO\Activity\ActivityFilterDTO;
use Growfund\DTO\Donation\AnnualReceiptFilterDTO;
use Growfund\DTO\Donation\DonationFilterParamsDTO;
use Growfund\DTO\Donor\CreateDonorDTO;
use Growfund\DTO\Donor\UpdateDonorDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\DonorPolicy;
use Growfund\Sanitizer;
use Growfund\Services\ActivityService;
use Growfund\Services\DonationService;
use Growfund\Services\DonorService;
use Growfund\Supports\Arr;
use Growfund\Supports\Money;
use Growfund\Validation\Validator;
use Exception;
use Growfund\Constants\HookNames;

class DonorController
{
    /**
     * @var DonorService
     */
    protected $service;

    /**
     * @var DonationService
     */
    protected $donation_service;

    /**
     * @var DonorPolicy
     */
    protected $policy;

    /**
     * Initialize the controller with DonorService.
     */
    public function __construct(DonorService $service, DonationService $donation_service, DonorPolicy $policy)
    {
        $this->service = $service;
        $this->donation_service = $donation_service;
        $this->policy = $policy;
    }

    /**
     * Get paginated donors
     * @param Request $request
     * @return Response
     */
    public function paginated(Request $request)
    {
        $this->policy->authorize_paginated();

        $donors = $this->service->paginated([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'search' => $request->get_string('search'),
            'orderby' => $request->get_column('orderby', 'ID', ['ID', 'user_email']),
            'order' => $request->get_string('order'),
            'start_date' => $request->get_date('start_date'),
            'end_date' => $request->get_date('end_date'),
            'status' => $request->get_string('status'),
            'campaign_id' => $request->get_int('campaign_id'),
        ]);

        return growfund_response()->json([
            'data' => $donors,
            'message' => '',
        ]);
    }

    /**
     * Create new donor
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $this->policy->authorize_create();

        $data = $request->all();

        $validator = Validator::make($data, CreateDonorDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, CreateDonorDTO::sanitization_rules())->get_sanitized_data();

        $donor_dto = CreateDonorDTO::from_array($sanitized_data);

        $donor_id = $this->service->store($donor_dto);

        $response = [
            'data' => ['id' => (string) $donor_id],
            'message' => __('Donor created successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update existing donor by ID
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $donor_id = $request->get_int('id');

        if (! growfund_user($donor_id)->is_donor()) {
            throw new Exception(esc_html__('Invalid donor ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_update($donor_id);

        $data = $request->all();

        $validator = Validator::make($data, UpdateDonorDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, UpdateDonorDTO::sanitization_rules())->get_sanitized_data();

        $donor_dto = UpdateDonorDTO::from_array($sanitized_data);

        $result = $this->service->update($donor_id, $donor_dto);

        $response = [
            'data' => $result,
            'message' => __('Donor updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Get the overview of a donor by ID
     * @param Request $request
     * @return Response
     */
    public function overview(Request $request)
    {
        $donor_id = $request->get_int('id');

        if (! growfund_user($donor_id)->is_donor()) {
            throw new Exception(esc_html__('Invalid donor ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_overview($donor_id);

        $data = $this->service->get_overview($donor_id);

        return growfund_response()->json([
            'data' => apply_filters(HookNames::GROWFUND_DONOR_OVERVIEW_FILTER, $data, $donor_id), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
            'message' => '',
        ]);
    }

    /**
     * Get the donations of a donor by ID
     * @param Request $request
     * @return Response
     */
    public function donations(Request $request)
    {
        $donation_params_dto = new DonationFilterParamsDTO();
        $donation_params_dto->user_id = $request->get_int('id');
        $donation_params_dto->page = $request->get_int('page', 1);
        $donation_params_dto->limit = $request->get_int('per_page', Pagination::LIMIT);

        $this->policy->authorize_donations($request->get_int('id'));

        return growfund_response()->json([
            'data' => $this->service->get_paginated_donations($donation_params_dto),
            'message' => '',
        ]);
    }

    /**
     * Delete donor
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $id = $request->get_int('id');

        if (! growfund_user($id)->is_donor()) {
            throw new Exception(esc_html__('Invalid donor ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $this->policy->authorize_delete($id);

        $delete_type = growfund_user()->get_id() === $id ? UserDeleteType::ANONYMIZE : UserDeleteType::TRASH;

        $is_deleted = $this->service->delete($id, $delete_type);

        $response = [
            'data' => $is_deleted,
            'message' => __('Donor deleted successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Empty soft donor
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
            ? __('Bulk action successfully applied for all the selected donor.', 'growfund')
            : sprintf(
                /* translators: %s: donor ids */
                __('Bulk action successfully applied for all the selected donor except the donor with id: %s.', 'growfund'),
                implode(', ', $failed)
            );

        return growfund_response()->json([
            'data' => $result,
            'message' => $message,
        ], Response::MULTI_STATUS);
    }

    public function get_stats()
    {
        $donor_id = growfund_user()->get_id();

        $this->policy->authorize_get_stats($donor_id);

        return growfund_response()->json([
            'data' => [
                'total_number_of_donations' => $this->donation_service->get_total_number_of_donations($donor_id),
                'total_supported_campaigns' => $this->donation_service->get_successfully_donated_campaigns_by_donor($donor_id),
                'total_contributions' => Money::prepare_for_display($this->donation_service->get_total_contribution_amount_by_donor($donor_id)),
                'average_contributions' => Money::prepare_for_display($this->donation_service->get_average_contribution_amount_by_donor($donor_id)),
            ],
            'message' => '',
        ]);
    }

    public function activities(Request $request)
    {
        $donor_id = $request->get_int('id');

        if (! growfund_user($donor_id)->is_donor()) {
            throw new Exception(esc_html__('Invalid donor ID.', 'growfund'), (int) Response::NOT_FOUND);
        }

        $activity_filter_dto = ActivityFilterDTO::from_array([
            'page' => $request->get_int('page', 1),
            'limit' => $request->get_int('per_page', 10),
            'orderby' => $request->get_column('orderby', 'created_at', ['ID', 'type', 'created_at']),
            'order' => $request->get_string('order', 'DESC'),
            'user_id' => $donor_id,
        ]);

        $activities = (new ActivityService())->paginated($activity_filter_dto, Activities::DONOR);

        return growfund_response()->json([
            'data' => $activities,
            'message' => '',
        ]);
    }


    public function get_annual_receipts(Request $request)
    {
        $this->policy->authorize_annual_receipts();

        $filter_dto = new AnnualReceiptFilterDTO();

        $filter_dto->page = $request->get_int('page', 1);
        $filter_dto->limit = $request->get_int('per_page', 10);

        return growfund_response()->json([
            'data' => $this->donation_service->get_annual_receipts_for_donor($filter_dto),
            'message' => '',
        ]);
    }

    public function get_annual_receipt_detail(Request $request)
    {
        $this->policy->authorize_annual_receipts();

        return growfund_response()->json([
            'data' => $this->donation_service->get_annual_receipt_detail(growfund_user()->get_id(), $request->get_int('year')),
            'message' => '',
        ]);
    }
}
