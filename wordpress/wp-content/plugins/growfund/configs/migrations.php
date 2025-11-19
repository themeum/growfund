<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Migrations\CreateActivityTable;
use Growfund\Migrations\CreateBookmarkTable;
use Growfund\Migrations\CreateCampaignCollaboratorTable;
use Growfund\Migrations\CreateDonationTable;
use Growfund\Migrations\CreateFundTable;
use Growfund\Migrations\CreatePledgeTable;

return [
    CreatePledgeTable::class,
    CreateCampaignCollaboratorTable::class,
    CreateFundTable::class,
    CreateDonationTable::class,
    CreateBookmarkTable::class,
    CreateActivityTable::class,
];
