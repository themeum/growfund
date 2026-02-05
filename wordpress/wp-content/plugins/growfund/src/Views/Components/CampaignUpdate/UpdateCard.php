<?php

namespace Growfund\Views\Components\CampaignUpdate;

use Growfund\DTO\CampaignPost\CampaignPostDTO;
use Growfund\View;

defined('ABSPATH') || exit;

class UpdateCard extends View {
    /** @var string */
    public $campaign_id;
 
        /** @var CampaignPostDTO */
    public $update;

    protected function get_template_dir() {
        return 'site/components/campaign-update';
    }

    public $casts = [
        'update' => CampaignPostDTO::class
    ];
}
