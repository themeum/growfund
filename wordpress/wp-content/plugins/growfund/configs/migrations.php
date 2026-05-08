<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Migrations\AddDeliveryOptionColumnInPledgeTable;
use Growfund\Migrations\AddEmailColumnInDonationTable;
use Growfund\Migrations\AddEmailColumnInPledgeTable;
use Growfund\Migrations\CreateActivityTable;
use Growfund\Migrations\CreateBookmarkTable;
use Growfund\Migrations\CreateCampaignCollaboratorTable;
use Growfund\Migrations\CreateCampaignSnapshotTable;
use Growfund\Migrations\CreateDonationTable;
use Growfund\Migrations\CreateFundTable;
use Growfund\Migrations\CreatePledgeTable;
use Growfund\Migrations\CreateWalletTable;
use Growfund\Migrations\CreateWalletTransactionTable;
use Growfund\Migrations\CreateWithdrawalItemsTable;
use Growfund\Migrations\CreateWithdrawalRequestTable;
use Growfund\Migrations\InsertUncategorizedInTermsTable;

/**
 * Database Migration Class list
 * Ordering is important. Migrations will be executed in order of this list
 */

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
    CreateCampaignSnapshotTable::class,
    CreateWithdrawalRequestTable::class,
    CreateWithdrawalItemsTable::class,
    CreateWalletTable::class,
    CreateWalletTransactionTable::class,
];
