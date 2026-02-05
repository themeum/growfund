<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\Campaign\CampaignFiltersDTO as CampaignFilterDTO;
use Growfund\Services\CampaignService;
use Growfund\Services\BookmarkService;
use Growfund\DTO\JsonResponseDTO;
use Growfund\PostTypes\Campaign;
use Growfund\Validation\Validator;
use Growfund\Views\Components\Campaign\CampaignList;

class CampaignController
{
    protected $campaign_service;
    protected $bookmark_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
        $this->bookmark_service = new BookmarkService();
    }

    public function paginated(Request $request) {
        $filters_dto = new CampaignFilterDTO();
        $filters_dto->page = $request->get_int('page', 1);
        $filters_dto->limit = $request->get_int('per_page', 12);
        $filters_dto->search = $request->get_string('search');
        $filters_dto->category_slug = $request->get_string('category_slug');
        $filters_dto->orderby = $request->get_column('orderby', 'ID', ['ID', 'post_title']);
        $filters_dto->order = $request->get_string('order');
        $filters_dto->status = 'launched-and-beyond';

        $paginated = $this->campaign_service->paginated($filters_dto);

        $campaign_list = new CampaignList();
        $campaign_list->campaigns = $paginated->results;
        $campaign_list->classname = 'growfund-ajax-campaign-list';

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($campaign_list),
            'data' => $paginated,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    public function paginated_featured_campaigns(Request $request) {
        $filters_dto = new CampaignFilterDTO();
        $filters_dto->page = $request->get_int('page', 1);
        $filters_dto->limit = $request->get_int('per_page', 6);
        $filters_dto->is_featured = 1;
        $filters_dto->status = 'launched-and-beyond';

        $paginated = $this->campaign_service->paginated($filters_dto);

        $campaign_list = new CampaignList();
        $campaign_list->campaigns = $paginated->results;
        $campaign_list->classname = 'growfund-ajax-campaign-list';

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($campaign_list),
            'data' => $paginated,
        ]);

        return growfund_site_response()->json($response_dto);
    }


    /**
     * Handle ajax request for bookmarking a campaign.
     *
     * @param Request $request
     * @return \Growfund\Http\Response
     */
    public function bookmark_campaign(Request $request)
    {
        $campaign_id = $request->get_int('campaign_id');
        $user_id = growfund_user()->get_id();

        $validator = Validator::make($request, [
            'campaign_id' => 'required|integer|post_exists:post_type=' . Campaign::NAME
        ]);

        if ($validator->is_failed()) {
            return growfund_site_response()->json_error($validator->get_errors());
        }

        $is_bookmarked = $this->bookmark_service->is_bookmarked($user_id, $campaign_id);

        if ($is_bookmarked) {
            $this->bookmark_service->remove_bookmark($user_id, $campaign_id);
            $is_bookmarked = false;
        } else {
            $this->bookmark_service->create_bookmark($user_id, $campaign_id);
            $is_bookmarked = true;
        }

        $response_dto = new JsonResponseDTO([
            'data' => [
                'campaign_id' => $campaign_id,
                'user_id' => $user_id,
                'is_bookmarked' => $is_bookmarked
            ],
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
