<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\CampaignPost\CampaignPostFilterDTO;
use Growfund\DTO\JsonResponseDTO;
use Growfund\Exceptions\ValidationException;
use Growfund\Policies\CampaignPostPolicy;
use Growfund\PostTypes\Campaign;
use Growfund\Services\CampaignPostService;
use Growfund\Validation\Validator;
use Growfund\Views\Components\Campaign\Tabs\UpdateContent;
use Growfund\Views\Components\CampaignUpdate\UpdateDetail;
use Growfund\Views\Components\CampaignUpdate\UpdateList;

/**
 * Class CampaignPostController
 * @since 1.0.0
 */
class CampaignPostController
{
    /**
     * @var CampaignPostService
     */
    protected $service;
    protected $policy;

    /**
     * Initialize the controller with CampaignPostService.
     */
    public function __construct()
    {
        $this->service = new CampaignPostService();
        $this->policy = new CampaignPostPolicy();
    }

    /**
     * Create campaign post
     * 
     * @param \Growfund\Http\Request $request
     * 
     * @return \Growfund\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'campaign_id'   => 'required|integer|post_exists:post_type=' . Campaign::NAME,
            'title'         => 'required|string',
            'slug'          => 'string',
            'description'   => 'string',
            'image'         => 'integer|is_valid_image_id',
        ]);


        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $this->policy->authorize_create($request->get_int('campaign_id'));

        $post_id = $this->service->save($request->get_int('campaign_id'), [
            'title'         => $request->get_string('title'),
            'slug'          => $request->get_string('slug'),
            'description'   => $request->get_html('description'),
            'image'         => $request->get_array('image'),
        ]);

        return growfund_response()->json([
            'data' => ['id' => (string) $post_id],
            'message' => __('Campaign post created successfully', 'growfund'),
        ]);
    }

    public function paginated_campaign_post_updates(Request $request) {
        $filters_dto = new CampaignPostFilterDTO();
        $filters_dto->page = $request->get_int('page', 1);
        $filters_dto->limit = 6;
        $filters_dto->campaign_id = $request->get_int('campaign_id');

        $paginated = $this->service->paginated($filters_dto);

        $update_collection = new UpdateList();
		$update_collection->updates = $paginated->results;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($update_collection),
            'data' => $paginated,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    public function get_campaign_post_update_detail(Request $request) {
        $campaign_update_id = $request->get_int('id');

        $campaign_update = $this->service->get_by_id($campaign_update_id);
       
		$neighbors = $this->service->get_neighbors(
        $campaign_update_id
		);

        $camapign_update_detail = new UpdateDetail();
        $camapign_update_detail->update = $campaign_update;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($camapign_update_detail),
            'data' => [
                'detail' => $campaign_update,
                'next_id' => $neighbors['next_id'],
				'prev_id' => $neighbors['prev_id']
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
