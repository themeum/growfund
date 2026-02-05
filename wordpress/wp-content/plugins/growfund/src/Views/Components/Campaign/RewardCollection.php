<?php

namespace Growfund\Views\Components\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\View;
use Growfund\DTO\RewardDTO;
use Growfund\DTO\Campaign\CampaignDTO;

class RewardCollection extends View {

    /** @var CampaignDTO */
    public $campaign;

    /** @var RewardDTO[] */
    public $rewards;

    /** @var string */
    public $classname;

	protected function get_template_dir() {
        return 'site/components/campaign';
    }

    public $casts = [
        'campaign' => CampaignDTO::class,
        'rewards.*' => RewardDTO::class
    ];
}
