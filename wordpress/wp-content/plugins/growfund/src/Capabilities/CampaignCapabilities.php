<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\PostTypes\Campaign;
use Growfund\Services\CampaignService;
use Growfund\Traits\HasConstants;

class CampaignCapabilities implements Capability
{
    use HasConstants;

    const CREATE            = 'growfund_create_campaign';
    const READ              = 'growfund_read_campaigns';
    const EDIT              = 'growfund_edit_campaign';
    const EDIT_OTHERS       = 'growfund_edit_others_campaigns';
    const DELETE            = 'growfund_delete_campaign';
    const DELETE_OTHERS     = 'growfund_delete_others_campaigns';

    protected $campaign_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
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
            case Backer::ROLE:
                return $this->contributor_capabilities();
            default:
                return [];
        }
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args): array
    {
        $capability_map = [
            static::EDIT => [$this, 'can_edit'],
            static::DELETE => [$this, 'can_delete'],
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
        ];
    }
    protected function contributor_capabilities()
    {
        return [
            static::READ,
        ];
    }

    protected function can_edit(int $user_id, int $campaign_id): array
    {
        $campaign = get_post($campaign_id);

        if (!$campaign || $campaign->post_type !== Campaign::NAME) {
            return ['do_not_allow'];
        }

        if ((int) $campaign->post_author === $user_id) {
            return ['exist'];
        }

        if ($this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return [static::EDIT_OTHERS];
    }

    protected function can_delete(int $user_id, int $campaign_id): array
    {
        $campaign = get_post($campaign_id);

        if (!$campaign || $campaign->post_type !== Campaign::NAME) {
            return ['do_not_allow'];
        }

        if (
            (int) $campaign->post_author === $user_id
        ) {
            return ['exist'];
        }

        return [static::DELETE_OTHERS];
    }
}
