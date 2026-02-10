<?php

namespace Growfund\Views\Components\Campaign;

use Growfund\View;
use Growfund\DTO\Campaign\CampaignDTO;

defined('ABSPATH') || exit;

class CampaignList extends View
{
    /** @var CampaignDto[] */
    public $campaigns;

    /** @var string */
    public $id;

    /** @var string */
    public $classname;

    /** @var bool */
    public $has_more = false;

    protected function get_template_dir()
    {
        return 'site/components/campaign';
    }

    public function get_casts()
    {
        return [
            'campaigns.*' => CampaignDto::class,
        ];
    }
}
