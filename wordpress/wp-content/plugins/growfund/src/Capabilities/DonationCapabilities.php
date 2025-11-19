<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Services\CampaignService;
use Growfund\Services\DonationService;
use Growfund\Traits\HasConstants;

class DonationCapabilities implements Capability
{
    use HasConstants;

    const CREATE            = 'growfund_create_donation';
    const READ              = 'growfund_read_donations';
    const EDIT              = 'growfund_edit_donation';
    const DELETE            = 'growfund_delete_donation';
    const EDIT_STATUS       = 'growfund_edit_donation_status';

    protected $campaign_service;
    protected $donation_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
        $this->donation_service = new DonationService();
    }

    public function handle()
    {
        add_filter('map_meta_cap', [$this, 'filter_capability'], 10, 4);
    }

    public function get_capabilities($role = null)
    {
        if (empty($role)) {
            return static::get_constant_values();
        }

        switch ($role) {
            case Fundraiser::ROLE:
                return $this->fundraiser_capabilities();
            case Donor::ROLE:
                return $this->donor_capabilities();
            default:
                return [];
        }
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args): array
    {
        $capability_map = [
            static::READ   => [$this, 'can_read'],
            static::CREATE   => [$this, 'can_create'],
            static::EDIT   => [$this, 'can_edit'],
            static::DELETE => [$this, 'can_delete'],
            static::EDIT_STATUS => [$this, 'can_edit_status'],
        ];

        if (isset($capability_map[$cap]) && isset($args[0])) {
            return call_user_func_array($capability_map[$cap], array_merge([$user_id], $args));
        }

        return $caps;
    }

    protected function fundraiser_capabilities()
    {
        return [
            static::CREATE,
            static::READ,
            static::EDIT,
            static::DELETE,
            static::EDIT_STATUS
        ];
    }

    protected function donor_capabilities()
    {
        return [
            static::READ,
            static::EDIT_STATUS
        ];
    }

    protected function can_read(int $user_id, $donation_id = null): array
    {
        if (empty($donation_id)) {
            return [static::READ];
        }

        $donation = $this->donation_service->get_by_id($donation_id);

        if (!$donation) {
            return ['do_not_allow'];
        }

        $campaign_id = $donation->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $donation->donor->id === $user_id) {
            return ['exist'];
        }

        if ((int) $campaign_author_id === $user_id || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_create(int $user_id, int $campaign_id): array
    {
        $is_campaign_creator = $this->campaign_service->get_author_id($campaign_id) === $user_id;

        if ($is_campaign_creator || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_edit(int $user_id, int $donation_id): array
    {
        $donation = $this->donation_service->get_by_id($donation_id);

        if (!$donation) {
            return ['do_not_allow'];
        }

        $campaign_id = $donation->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $donation->donor->id === $user_id) {
            return ['exist'];
        }

        if ((int) $campaign_author_id === $user_id || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, $donation_id = null): array
    {
        if (empty($donation_id)) {
            return [static::DELETE];
        }

        $donation = $this->donation_service->get_by_id($donation_id);

        if (!$donation) {
            return ['do_not_allow'];
        }

        $campaign_id = $donation->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $campaign_author_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_edit_status(int $user_id, int $donation_id)
    {
        $donation = $this->donation_service->get_by_id($donation_id);

        if (!$donation) {
            return ['do_not_allow'];
        }

        $user = growfund_user($user_id);

        if ($user->is_donor() && (int) $donation->donor->id === $user_id) {
            return ['exist'];
        }

        if (
            $user->is_fundraiser()
            && (
                (int) $donation->campaign->author_id === $user_id
                || $this->campaign_service->is_collaborator($user_id, $donation->campaign->id)
            )
        ) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
