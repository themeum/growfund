<?php

namespace Growfund\DTO\CampaignPost;

defined( 'ABSPATH' ) || exit;



class CampaignPostFilterDTO {
    /** @var int */
    public $page = 1;

    /** @var int */
    public $limit = 6;

    /** @var int */
    public $campaign_id;

    /** @var string */
    public $orderby = 'date';

    /** @var string */
    public $order = 'DESC';
}
