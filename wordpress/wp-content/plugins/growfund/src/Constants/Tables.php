<?php

namespace Growfund\Constants;

defined( 'ABSPATH' ) || exit;

class Tables
{
    /**
     * Table name for pledges
     */
    const PLEDGES = 'growfund_pledges';

    /**
     * Table name for donations
     */
    const DONATIONS = 'growfund_donations';

    /**
     * Table name for campaign collaborator
     */
    const CAMPAIGN_COLLABORATORS = 'growfund_campaign_collaborators';

    /**
     * Table name for funds
     */
    const FUNDS = 'growfund_funds';

    /**
     * Table name for activities of user, campaign and pledge, donation etc
     */
    const ACTIVITIES = 'growfund_activities';

    /**
     * Table name for storing the bookmarked campaigns by backers/donors
     */
    const BOOKMARKS = 'growfund_bookmarks';
}
