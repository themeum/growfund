<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\RewardDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\RewardPolicy;
use Growfund\PostTypes\Campaign as CampaignPostType;
use Growfund\Sanitizer;
use Growfund\Services\RewardService;
use Growfund\Validation\Validator;
use Exception;

/**
 * Class RewardController
 * 
 * @since 1.0.0
 */
class RewardController
{
    /**
     * @var RewardService
     */
    private $service;

    /**
     * @var RewardPolicy
     */
    private $policy;

    /**
     * RewardController constructor.
     */
    public function __construct(RewardService $service, RewardPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Create rewards
     * @param Request $request
     * @return Response 
     */

    public function create(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');

        if (get_post_type($campaign_id) !== CampaignPostType::NAME) {
            throw new Exception(esc_html__('Campaign not found', 'growfund'), (int) Response::NOT_FOUND);
        }

        $data = $request->all();

        $validator = Validator::make($data, RewardDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_create($campaign_id);

        $sanitized_data = Sanitizer::make($data, RewardDTO::sanitization_rules())
            ->get_sanitized_data();

        $dto = RewardDTO::from_array($sanitized_data);

        $reward_id = $this->service->create($campaign_id, $dto);

        $response = [
            'data' => ['id' => (string) $reward_id],
            'message' => __("Reward created successfully!", "growfund")
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update existing rewards
     * 
     * @param Request $request
     * @return Response 
     */
    public function update(Request $request)
    {
        $reward_id = $request->get_int('reward_id');

        $data = $request->all();

        $validator = Validator::make($data, RewardDTO::validation_rules());

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_update($reward_id);

        $sanitized_data = Sanitizer::make($data, RewardDTO::sanitization_rules())
            ->get_sanitized_data();

        $dto = RewardDTO::from_array($sanitized_data);

        $is_updated = $this->service->update($reward_id, $dto);

        $response = [
            'data' => $is_updated,
            'message' => __("Reward updated successfully!", "growfund")
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Delete reward
     * 
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $this->policy->authorize_delete($request->get_int('reward_id'));

        $is_deleted = $this->service->delete($request->get_int('reward_id'));

        $response = [
            'data' => $is_deleted,
            'message' => __('Reward deleted successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Get all rewards list
     * @param Request $request
     * @return Response
     */
    public function list(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');

        $this->policy->authorize_list($campaign_id);

        return growfund_response()->json([
            'data' => $this->service->get_all($campaign_id),
            'message' => '',
        ]);
    }
}
