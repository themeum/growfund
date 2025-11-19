<?php

namespace Growfund\Services\Migration;

defined( 'ABSPATH' ) || exit;

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
                Option::set(OptionKeys::IS_MIGRATED_FROM_CROWDFUNDING, true);

                return $response;
            default:
                return false;
        }
    }
}
