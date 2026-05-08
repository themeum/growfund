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

    /**
     * Table name for storing the withdrawal requests by fundraisers
     */
    const WITHDRAWAL_REQUESTS = 'growfund_withdrawal_requests';

    /**
     * Table name for storing the withdrawal to specific campaign
     */
    const WITHDRAWAL_ITEMS = 'growfund_withdrawal_items';

    /**
     * Table name for storing the wallet information of fundraisers
     */
    const WALLETS = 'growfund_wallets';

    /**
     * Table name for storing the wallet transaction history
     */
    const WALLET_TRANSACTIONS = 'growfund_wallet_transactions';

    /**
     * Table name for storing campaign snapshots
     */
    const CAMPAIGN_SNAPSHOTS = 'growfund_campaign_snapshots';
}
