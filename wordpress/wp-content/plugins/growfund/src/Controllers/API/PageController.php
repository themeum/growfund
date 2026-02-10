<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Http\Response;
use Growfund\Services\PageService;

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
        return growfund_response()->json([
            'data' => $this->service->all(),
            'message' => '',
        ], Response::OK);
    }

    /**
     * Retrieve all pages.
     *
     * @return Response JSON response containing all growfund pages data.
     */

    public function growfund_pages()
    {
        return growfund_response()->json([
            'data' => $this->service->get_growfund_pages(),
            'message' => '',
        ], Response::OK);
    }

    public function re_generate_pages()
    {
        return growfund_response()->json([
			'data' => $this->service->generate_growfund_pages(),
			'message' => __('Pages regenerated successfully', 'growfund'),
		], Response::CREATED);
    }
}
