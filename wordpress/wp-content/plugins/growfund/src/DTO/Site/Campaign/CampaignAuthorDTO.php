<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

class CampaignAuthorDTO extends DTO
{
    /** @var string|null */
    public $name;

    /** @var string|null */
    public $image;

    /** @var string|null */
    public $description;

    /** @var int */
    public $campaign_count = 0;

    /** @var int */
    public $pledge_count = 0;
}
