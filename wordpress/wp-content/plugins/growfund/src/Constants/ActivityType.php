<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

class ActivityType
{
    const TIMELINE = 'timeline';

    const CAMPAIGN_SUBMITTED_FOR_REVIEW = 'campaign-submitted-for-review';
    const CAMPAIGN_RE_SUBMITTED_FOR_REVIEW = 'campaign-re-submitted-for-review';
    const CAMPAIGN_DECLINED = 'campaign-declined';
    const CAMPAIGN_APPROVED_AND_PUBLISHED = 'campaign-approved-and-published';
    const CAMPAIGN_SET_DEADLINE = 'campaign-set-deadline';
    const CAMPAIGN_REMOVED_DEADLINE = 'campaign-removed-deadline';
    const CAMPAIGN_EXTENDED_DEADLINE = 'campaign-extended-deadline';
    const CAMPAIGN_MARKED_AS_COMPLETED = 'campaign-marked-as-completed';

    const CAMPAIGN_POST_UPDATE = 'campaign-post-update';
    const CAMPAIGN_GOAL_REACHED = 'campaign-goal-reached';
    const CAMPAIGN_COMMENT = 'commented-on-the-campaign';

    const PLEDGE_CREATION = 'pledge-created';
    const PLEDGE_CANCELLED = 'pledge-cancelled';
    const PLEDGE_BACKED = 'pledge-backed';
    const PLEDGE_FAILED_TO_BACK = 'pledge-failed-to-back';
    const PLEDGE_COMPLETED = 'pledge-completed';

    const PLEDGE_REFUND_REQUESTED = 'pledge-refund-requested'; //@todo: need to implement
    const PLEDGE_REFUND_RECEIVED = 'pledge-refund-received'; //@todo: need to implement

    const DONATION_CREATION = 'donation-created';
    const DONATION_CANCELLED = 'donation-cancelled';
    const DONATION_FAILED = 'donation-failed';
    const DONATION_COMPLETED = 'donation-completed';

    const DONATION_REFUND_REQUESTED = 'donation-refund-requested'; //@todo: need to implement
    const DONATION_REFUND_RECEIVED = 'donation-refund-received'; //@todo: need to implement
}
