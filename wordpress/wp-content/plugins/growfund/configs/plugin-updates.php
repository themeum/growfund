<?php

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
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
use Growfund\Managers\MigrationManager;
use Growfund\Supports\Option;

return [
    '1.0.0' => function() {
        $migrations = [
            CreatePledgeTable::class,
            CreateFundTable::class,
            CreateDonationTable::class,
            CreateCampaignCollaboratorTable::class,
            CreateBookmarkTable::class,
            CreateActivityTable::class,
            InsertUncategorizedInTermsTable::class,
        ];

        MigrationManager::run($migrations);
    },
    '1.0.2' => function() {
        $migrations = [
            AddEmailColumnInPledgeTable::class,
            AddEmailColumnInDonationTable::class,
        ];

        MigrationManager::run($migrations);
    },
    '1.0.9' => function() {
        $migrations = [
            AddDeliveryOptionColumnInPledgeTable::class,
        ];

        MigrationManager::run($migrations);
    },
    '1.1.0' => function() {
        $migrations = [
            CreateCampaignSnapshotTable::class,
            CreateWithdrawalRequestTable::class,
            CreateWithdrawalItemsTable::class,
            CreateWalletTable::class,
            CreateWalletTransactionTable::class,
        ];

        MigrationManager::run($migrations);
        Option::update_default_settings();
        Option::store_default_email_templates('growfund_email_verification');

        $is_sync_enabled = true;
        do_action(HookNames::GROWFUND_WALLET_TRANSACTION_SYNC_ACTION, $is_sync_enabled);
    },
];
