<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;

/**
 * DTO for Campaign Template Data
 * 
 * Handles template data for both archive and single campaign pages
 */
class CampaignTemplateDTO extends DTO
{
    /** @var array|null */
    public $data;

    /** @var array|null */
    public $featured_data;

    /** @var int */
    public $initial_limit = 12;

    /** @var int */
    public $featured_initial_limit = 10;

    /** @var array */
    public $filter_state;

    /** @var string */
    public $campaign_id;
}
