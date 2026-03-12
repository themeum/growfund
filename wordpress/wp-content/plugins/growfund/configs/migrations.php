<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Migrations\AddDeliveryOptionColumnInPledgeTable;
use Growfund\Migrations\AddEmailColumnInDonationTable;
use Growfund\Migrations\AddEmailColumnInPledgeTable;
use Growfund\Migrations\CreateActivityTable;
use Growfund\Migrations\CreateBookmarkTable;
use Growfund\Migrations\CreateCampaignCollaboratorTable;
use Growfund\Migrations\CreateDonationTable;
use Growfund\Migrations\CreateFundTable;
use Growfund\Migrations\CreatePledgeTable;
use Growfund\Migrations\InsertUncategorizedInTermsTable;

return [
    CreatePledgeTable::class,
    CreateCampaignCollaboratorTable::class,
    CreateFundTable::class,
    CreateDonationTable::class,
    CreateBookmarkTable::class,
    CreateActivityTable::class,
    InsertUncategorizedInTermsTable::class,
    AddEmailColumnInPledgeTable::class,
    AddEmailColumnInDonationTable::class,
    AddDeliveryOptionColumnInPledgeTable::class,
];
