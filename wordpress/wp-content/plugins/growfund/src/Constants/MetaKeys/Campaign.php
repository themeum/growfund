<?php

namespace Growfund\Constants\MetaKeys;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Constant;
use Growfund\Traits\HasConstants;

/**
 * Campaign meta keys
 * 
 * @since 1.0.0
 */
class Campaign implements Constant
{
    use HasConstants;

    const STORY = 'story';
    const IMAGES = 'images';
    const VIDEO = 'video';
    const CATEGORY = 'category';
    const SUB_CATEGORY = 'sub_category';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const LOCATION = 'location';
    const TAGS = 'tags';
    const COLLABORATORS = 'collaborators';
    const SHOW_COLLABORATOR_LIST = 'show_collaborator_list';
    const STATUS = 'status';
    const RISK = 'risk';
    const HAS_TRIBUTE = 'has_tribute';
    const HAS_GOAL = 'has_goal';
    const GOAL_TYPE = 'goal_type';
    const REACHING_ACTION = 'reaching_action';
    const CONFIRMATION_TITLE = 'confirmation_title';
    const CONFIRMATION_DESCRIPTION = 'confirmation_description';
    const PROVIDE_CONFIRMATION_PDF_RECEIPT = 'provide_confirmation_pdf_receipt';
    const GOAL_AMOUNT = 'goal_amount';
    const APPRECIATION_TYPE = 'appreciation_type';
    const REWARDS = 'rewards';
    const ALLOW_PLEDGE_WITHOUT_REWARD = 'allow_pledge_without_reward';
    const MIN_PLEDGE_AMOUNT = 'min_pledge_amount';
    const MAX_PLEDGE_AMOUNT = 'max_pledge_amount';
    const FUNDRAISER_ID = 'fundraiser_id';

    /**
     * Get all campaign meta keys
     * 
     * @return array
     */
    public static function all()
    {
        return static::get_constant_values();
    }
}
