<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Policies\CampaignPostPolicy;
use Growfund\PostTypes\Campaign;
use Growfund\Services\CampaignPostService;
use Growfund\Validation\Validator;

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
    public function __construct(CampaignPostService $service, CampaignPostPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
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
}
