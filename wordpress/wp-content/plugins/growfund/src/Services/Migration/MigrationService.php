<?php

namespace Growfund\Services\Migration;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\OptionKeys;
use Growfund\Supports\Option;

class MigrationService
{
    public function migrate(string $step)
    {
        $campaign_migration_service = new CampaignMigrationService();
        $donation_migration_service = new DonationMigrationService();
        $pledge_migration_service = new PledgeMigrationService();

        switch ($step) {
			case 'migrate-campaigns':
                return $campaign_migration_service->migrate();
            case 'migrate-contributions':
                return growfund_app()->is_donation_mode() ? $donation_migration_service->migrate() : $pledge_migration_service->migrate();
            case 'final':
                $response = $campaign_migration_service->change_post_type();

                $campaign_migration_service->remove_migration_data();
                $donation_migration_service->remove_migration_data();
                $pledge_migration_service->remove_migration_data();
                
                Option::set(OptionKeys::IS_MIGRATED_FROM_CROWDFUNDING, true);

                do_action(HookNames::WP_CROWDFUNDING_DEACTIVATE, true, 'growfund');
                
                return $response;
            default:
                return false;
        }
    }
}
