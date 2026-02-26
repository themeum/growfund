<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Services\RewardItemService;

class RewardItemController
{
    /**
     * @var RewardItemService
     */
    private $service;

    public function __construct() {
        $this->service = new RewardItemService();
    }


    public function download_reward_item(Request $request) {
        $this->service->handle_reward_item_secure_download(
            $request->get_string('uid'), 
            $request->get_int('reward_item_id'), 
            $request->get_string('signature'), 
            $request->get_int('expires'),
            $request->get_int('user')
        );

        exit;
    }
}
