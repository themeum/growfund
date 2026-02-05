<?php

namespace Growfund\Views\Components\CampaignUpdate;

use Growfund\View;
use Growfund\DTO\CampaignPost\CampaignPostDTO;

defined('ABSPATH') || exit;

class UpdateDetailContainer extends View {

    /** 
     * @var string 
     */
    public $campaign_id;

    /** 
     * @var CampaignPostDTO[] 
     */
    public $updates = [];

    /**
     * @var int
     */
    public $total_count = 0;

    /**
     * @var string|null
     */
    public $active_update_id = null;

	protected function get_template_dir() {
        return 'site/components/campaign-update';
    }

    public $casts = [

        'updates.*' => CampaignPostDTO::class

    ];
}
