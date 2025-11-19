<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Activities;
use Growfund\Constants\ActivityType;
use Growfund\Constants\Tables;
use Growfund\Contracts\Request;
use Growfund\DTO\Activity\ActivityDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\DonationPolicy;
use Growfund\Policies\PledgePolicy;
use Growfund\Services\TimelineService;
use Growfund\Validation\Validator;
use Exception;

class TimelineController
{
    /**
     * @var TimelineService
     */
    protected $service;

    protected $policy;

    /**
     * Initialize class with TimelineService
     */
    public function __construct(TimelineService $service, PledgePolicy $pledge_policy, DonationPolicy $donation_policy)
    {
        $this->service = $service;

        if (growfund_app()->is_donation_mode()) {
            $this->policy = $donation_policy;
        } else {
            $this->policy = $pledge_policy;
        }
    }

    /**
     * Create a new timeline comment
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\Response
     */
    public function create(Request $request)
    {
        $contribution_name = $request->get_string('contribution_name');

        $validator = Validator::make($request->all(), [
            'contribution_id' => 'required|integer|exists:' . ($contribution_name === 'donations' ? Tables::DONATIONS : Tables::PLEDGES) . ',ID',
            'comment' => 'required|string'
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update_status($request->get_int('contribution_id'), growfund_user()->get_id());

        $dto = new ActivityDTO();
        $dto->type = ActivityType::TIMELINE;

        if ($contribution_name === 'pledges') {
            $dto->pledge_id = $request->get_int('contribution_id');
        }

        if ($contribution_name === 'donations') {
            $dto->donation_id = $request->get_int('contribution_id');
        }

        $dto->data = wp_json_encode([
            'comment' => $request->get_string('comment')
        ]);

        $timeline_id = $this->service->save($dto);

        return growfund_response()->json([
            'data' => ['id' => (string) $timeline_id],
            'message' => __('Comment created successfully', 'growfund'),
        ], Response::CREATED);
    }


    /**
     * Delete a timeline comment
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\Response
     */
    public function delete(Request $request)
    {
        $activity = growfund_app()->is_donation_mode() ? Activities::DONATION : Activities::PLEDGE;
        $timeline = $this->service->get_by_id($request->get_int('timeline_id'), $activity);

        $contribution_name = $request->get_string('contribution_name');
        $contribution_id = $request->get_int('contribution_id');

        $this->policy->authorize_update_status(growfund_user()->get_id(), $request->get_int('fund_type_id'));

        if (
            empty($timeline)
            || ($contribution_name === 'pledges' && (int) $timeline->pledge_id !==  $contribution_id)
            || ($contribution_name === 'donations' && (int) $timeline->donation_id !==  $contribution_id)
        ) {
            throw new Exception(esc_html__('Comment not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $is_deleted = $this->service->delete_by_id((int) $timeline->id);

        return growfund_response()->json([
            'data' => $is_deleted,
            'message' => $is_deleted ? __('Comment deleted successfully', 'growfund') : __('Comment not found', 'growfund'),
        ], $is_deleted ? Response::NO_CONTENT : Response::NOT_FOUND);
    }
}
