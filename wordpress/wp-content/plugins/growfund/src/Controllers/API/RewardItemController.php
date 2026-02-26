<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\RewardItemDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\RewardItemPolicy;
use Growfund\Sanitizer;
use Growfund\Services\RewardItemService;
use Growfund\Validation\Validator;

/**
 * Handles operations related to reward items.
 *
 * @since 1.0.0
 */
class RewardItemController
{
    /**
     * Reward item service instance.
     *
     * @var RewardItemService
     */
    protected $service;

    /**
     * Reward item policy instance.
     *
     * @var RewardItemPolicy
     */
    protected $policy;

    /**
     * Initialize the controller with RewardItemService.
     */
    public function __construct(RewardItemService $service, RewardItemPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Get all reward items by its parent campaign's id.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function all(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|integer',
        ]);

        if (!$validator->is_valid()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_all($request->get_int('campaign_id'));

        return growfund_response()->json([
            'data' => $this->service->get_all_by_campaign($request->get_int('campaign_id')),
            'message' => '',
        ]);
    }

    /**
     * Create a new reward item.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     * @throws ValidationException
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), RewardItemDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_create($request->get_int('campaign_id'));

        $sanitized_data = Sanitizer::make($request->all(), RewardItemDTO::sanitization_rules())->get_sanitized_data();

        $id = $this->service->store(RewardItemDTO::from_array($sanitized_data));

        $response = [
            'data' => ['id' => (string) $id],
            'message' => __('Reward item created successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update reward item
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), RewardItemDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update($request->get_int('id'));

        $sanitized_data = Sanitizer::make($request->all(), RewardItemDTO::sanitization_rules())->get_sanitized_data();

        $is_updated = $this->service->update(
            $request->get_int('id'),
            RewardItemDTO::from_array($sanitized_data)
        );

        $response = [
            'data' => $is_updated,
            'message' => __('Reward item updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    /**
     * Delete reward item
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $id = $request->get_int('id');

        $this->policy->authorize_delete($id);

        $is_deleted = $this->service->delete($campaign_id, $id);

        $response = [
            'data' => $is_deleted,
            'message' => __('Reward item deleted successfully.', 'growfund'),
        ];

        return growfund_response()->json($response);
    }

    public function get_download_link(Request $request) {
        $reward_item_id = $request->get_int('id');
        $pledge_uid = $request->get_string('uid');

        return growfund_response()->json([
            'data' => [
                'download_link' => $this->service->get_downloadable_link_for_digital_goods($pledge_uid, $reward_item_id)
            ]
        ]);
    }
}
