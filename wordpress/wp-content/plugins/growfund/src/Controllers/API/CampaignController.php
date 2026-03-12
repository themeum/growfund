<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\Status\CampaignSecondaryStatus;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Contracts\Request;
use Growfund\Core\AppSettings;
use Growfund\DTO\Campaign\UpdateCampaignDTO;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\CampaignPolicy;
use Growfund\PostTypes\Campaign;
use Growfund\Sanitizer;
use Growfund\Services\CampaignService;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;
use Growfund\Validation\Validator;

/**
 * Handles operations related to campaigns.
 *
 * @since 1.0.0
 */
class CampaignController
{
    /**
     * Campaign service instance.
     *
     * @var CampaignService
     */
    protected $service;

    /**
     * PledgeService instance
     * @var PledgeService
     */
    protected $pledge_service;

    protected $policy;

    /**
     * Initialize the controller with CampaignService.
     */
    public function __construct(CampaignService $service, CampaignPolicy $policy, PledgeService $pledge_service)
    {
        $this->service = $service;
        $this->pledge_service = $pledge_service;
        $this->policy = $policy;
    }

    public function overview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|post_exists:post_type=" . Campaign::NAME,
            "start_date" => "nullable|date_format:Y-m-d",
            "end_date" => "nullable|date_format:Y-m-d",
        ]);

        $this->policy->authorize_overview($request->get_int('id'));

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $start_date = $request->get_date('start_date');
        $end_date = $request->get_date('end_date');

        if (empty($start_date) && empty($end_date)) {
            list($start_date, $end_date) = Date::start_and_end_date_of_last_thirty_days();
        }

        if (empty($end_date)) {
            $end_date = $start_date;
        }

        return growfund_response()->json([
            'data' => $this->service->get_overview_details($request->get_int('id'), $start_date, $end_date),
            'message' => '',
        ]);
    }

    /**
     * Return a paginated list of campaigns.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function paginated(Request $request)
    {
        $this->policy->authorize_paginated();

        $filters_dto = new CampaignFiltersDTO();
        $filters_dto->page = $request->get_int('page', 1);
        $filters_dto->limit = $request->get_int('per_page', 10);
        $filters_dto->search = $request->get_string('search');
        $filters_dto->status = $request->get_string('status');
        $filters_dto->start_date = $request->get_date('start_date');
        $filters_dto->end_date = $request->get_date('end_date');

        if (!growfund_user()->is_admin()) {
            $filters_dto->author_id = growfund_user()->get_id();
        }

        if (growfund_user()->is_admin() && $request->get_string('fundraiser_id')) {
            $filters_dto->author_id = $request->get_string('fundraiser_id');
        }

        return growfund_response()->json([
            'data' => $this->service->paginated($filters_dto),
            'message' => '',
        ]);
    }

    /**
     * Get campaign by ID.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function get_by_id(Request $request)
    {
        $this->policy->authorize_get_by_id($request->get_int('id'));

        return growfund_response()->json([
            'data' => $this->service->get_by_id($request->get_int('id')),
            'message' => '',
        ]);
    }

    /**
     * Create a new campaign.
     *
     * @return \Growfund\Http\Response
     */
    public function create() 
    {
        $this->policy->authorize_create();

        $id = $this->service->create();

        $response = [
            'data' => ['id' => (string) $id],
            'message' => __('Campaign created successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update an existing campaign.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, UpdateCampaignDTO::validation_rules());

        $validator->apply_if('rewards', 'required|array', function ($data) {
            return $data['status'] === CampaignStatus::PUBLISHED &&
                $data['appreciation_type'] === AppreciationType::GOODIES;
        });

        $validator->apply_if('giving_thanks', 'required|array', function ($data) {
            return $data['status'] === CampaignStatus::PUBLISHED &&
                $data['appreciation_type'] === AppreciationType::GIVING_THANKS;
        });

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, UpdateCampaignDTO::sanitization_rules())->get_sanitized_data();

        $campaign_dto = UpdateCampaignDTO::from_array($sanitized_data);

        if (growfund_user()->is_fundraiser()) {
            if ($campaign_dto->status === CampaignStatus::PUBLISHED && !growfund_settings(AppSettings::PERMISSIONS)->fundraisers_can_publish_campaigns()) {
                $campaign_dto->status = CampaignStatus::PENDING;
            }
        }

        $this->policy->authorize_update($campaign_dto->id, $campaign_dto);

        $is_updated = $this->service->update($campaign_dto->id, $campaign_dto);

        $response = [
            'data' => $is_updated,
            'message' => __('Campaign updated successfully', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    /**
     * Delete a campaign
     * 
     * @param \Growfund\Contracts\Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $this->policy->authorize_delete($request->get_int('id'));

        $is_deleted = $this->service->delete($request->get_int('id'));

        $response = [
            'data' => $is_deleted,
            'message' => __('Campaign deleted successfully', 'growfund'),
        ];

        return growfund_response()->json($response);
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
            'action'    => 'required|string|in:trash,restore,delete,featured,non-featured',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_bulk_actions($request->get_array('ids'), $request->get_string('action'));

        $result = [];

        switch ($request->get_string('action')) {
            case 'trash':
                $result = $this->service->bulk_delete($request->get_array('ids'));
                break;
            case 'delete':
                $force_delete = true;
                $result = $this->service->bulk_delete($request->get_array('ids'), $force_delete);
                break;
            case 'restore':
                $result = $this->service->bulk_restore($request->get_array('ids'));
                break;
            case 'featured':
                $result = $this->service->bulk_featured($request->get_array('ids'));
                break;
            case 'non-featured':
                $result = $this->service->bulk_non_featured($request->get_array('ids'));
                break;
        }


        $failed = empty($result['failed']) ? [] : Arr::make($result['failed'])->pluck('id')->toArray();

        $message = empty($failed)
            ? __('Bulk action successfully applied for all the selected campaigns.', 'growfund')
            : sprintf(
                /* translators: %s: Campaign ids */
                __('Bulk action successfully applied for all the selected campaigns except the campaigns with id: %s.', 'growfund'),
                implode(', ', $failed)
            );

        return growfund_response()->json([
            'data' => $result,
            'message' => $message,
        ], Response::MULTI_STATUS);
    }

    /**
     * Empty trash campaigns
     *
     * @return Response
     */
    public function empty_trash()
    {
        $this->policy->authorize_empty_trash();

        $is_deleted = $this->service->empty_trash(growfund_user()->get_id());

        if (!$is_deleted) {
            return growfund_response()->json([
                'errors' => [],
                'message' => __('Failed to empty trash', 'growfund'),
            ], Response::INTERNAL_SERVER_ERROR);
        }

        return growfund_response()->json([
            'data' => $is_deleted,
            'message' => __('Trash emptied successfully', 'growfund'),
        ]);
    }

    /**
     * Update campaign status
     *
     * @param Request $request
     * @return Response
     */
    public function update_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                => 'required',
            'status'            => 'required|string|in:' . implode(',', CampaignStatus::get_constant_values()),
            'decline_reason'    => 'required_if:status,declined|string',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update_status($request->get_int('id'), $request->get_string('status'));

        $is_updated = $this->service->update_status(
            $request->get_int('id'),
            $request->get_string('status'),
            $request->get_text('decline_reason')
        );

        return growfund_response()->json([
            'data' => $is_updated,
            'message' => __('Campaign status updated successfully', 'growfund'),
        ]);
    }

    /**
     * Update the secondary statuses of a campaign.
     * The available statuses are ended, hidden, visible, pause, resume.
     *
     * @param Request $request
     * @return void
     */
    public function update_secondary_status(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', CampaignSecondaryStatus::get_constant_values()),
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_admin_only();

        $is_updated = $this->service->update_secondary_status(
            $request->get_int('id'),
            $request->get_string('status')
        );

        $messages = [
            CampaignSecondaryStatus::END => __('Campaign marked as ended', 'growfund'),
            CampaignSecondaryStatus::HIDE => __('Campaign visibility changed to hidden', 'growfund'),
            CampaignSecondaryStatus::VISIBLE => __('Campaign visibility changed to visible', 'growfund'),
            CampaignSecondaryStatus::PAUSE => __('Campaign pledging paused', 'growfund'),
            CampaignSecondaryStatus::RESUME => __('Campaign pledging resumed', 'growfund'),
        ];

        return growfund_response()->json([
            'data' => $is_updated,
            'message' => $messages[$request->get_string('status')],
        ]);
    }

    /**
     * Charge backers of a campaign
     *
     * @param Request $request
     * @return Response
     */
    public function charge_backers(Request $request)
    {
        $this->policy->authorize_admin_only();

        $campaign_id = $request->get_int('campaign_id');

        $is_charged = $this->pledge_service->charge_backers($campaign_id);

        return growfund_response()->json([
            'data' => $is_charged,
            'message' => __('Backers charged successfully', 'growfund'),
        ]);
    }

    public function ready_for_pickup(Request $request)
    {
        $this->policy->authorize_mark_as_ready_for_pickup($request->get_int('campaign_id'));

        $is_ready = $this->service->mark_campaign_as_ready_for_pickup($request->get_int('campaign_id'));

        return growfund_response()->json([
            'data' => $is_ready,
            'message' => __('Marked all pledges as ready for pickup successfully', 'growfund'),
        ]);
    }
}
