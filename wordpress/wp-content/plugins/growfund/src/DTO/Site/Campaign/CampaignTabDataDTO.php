<?php

namespace Growfund\DTO\Site\Campaign;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\DTO;
use Growfund\DTO\Site\Campaign\TimelineDatesDTO;

/**
 * DTO for Campaign Tab Data
 * 
 * Pure data transfer object for campaign tab data (updates, rewards, etc.)
 */
class CampaignTabDataDTO extends DTO
{
    /** @var string */
    public $campaign_id;

    /** @var array */
    public $rewards;

    /** @var array */
    public $campaign_updates;

    /** @var TimelineDatesDTO */
    public $timeline_dates;

    /** @var int */
    public $total_campaign_updates_count = 0;

    /** @var int */
    public $total_comments_count = 0;

    /** @var array */
    public $update_navigation;

    /** @var array */
    public $comments;

    /** @var array */
    public $comment_form_data;
}
