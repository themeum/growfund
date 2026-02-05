<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Http\Response;
use Growfund\Services\PageService;
use WP_User_Query;

class PageController
{
    protected $service;

    /**
     * Initialize the controller with PageService.
     */
    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    /**
     * Retrieve all pages.
     *
     * @return Response JSON response containing all pages data.
     */

    public function all()
    {
        $results = $this->service->all();

        return growfund_response()->json([
            'data' => $results,
            'message' => '',
        ], Response::OK);
    }
}
