<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\DTO\Campaign\BookmarkFilterDTO;
use Growfund\Http\Response;
use Growfund\Policies\BookmarkPolicy;
use Growfund\Services\BookmarkService;

class BookmarksController
{
    /**
     * @var BookmarkService
     */
    protected $service;

    /**
     * @var BookmarkPolicy
     */
    protected $policy;

    /**
     * Initialize class with TimelineService
     */
    public function __construct(BookmarkService $service, BookmarkPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Get the paginated bookmarked campaigns for a specific user.
     *
     * @param Request $request
     * @return Response
     */
    public function paginated(Request $request)
    {
        $dto = new BookmarkFilterDTO();
        $dto->page = $request->get_int('page', 1);
        $dto->limit = $request->get_int('limit', 10);
        $dto->search = $request->get_string('search');
        $dto->user_id = $request->get_int('user_id');

        $this->policy->authorize_paginated($request->get_int('user_id'));

        $bookmarks = $this->service->paginated($dto);

        return growfund_response()->json([
            'data' => $bookmarks,
            'message' => '',
        ]);
    }

    /**
     * Remove a campaign from the user's bookmarks.
     *
     * @param Request $request
     * @return Response
     */
    public function remove(Request $request)
    {
        $user_id = $request->get_int('user_id');
        $campaign_id = $request->get_int('campaign_id');

        $this->policy->authorize_remove($user_id);

        $is_removed = $this->service->remove_bookmark($user_id, $campaign_id);

        if (!$is_removed) {
            return growfund_response()->json([
                'message' => __('Failed to remove bookmark', 'growfund'),
            ], Response::INTERNAL_SERVER_ERROR);
        }

        return growfund_response()->json([
            'data' => $is_removed,
            'message' => __('Bookmark removed successfully', 'growfund'),
        ]);
    }
}
