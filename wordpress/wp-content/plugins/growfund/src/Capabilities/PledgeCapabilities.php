<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Services\CampaignService;
use Growfund\Services\PledgeService;
use Growfund\Traits\HasConstants;

class PledgeCapabilities implements Capability
{
    use HasConstants;

    const CREATE = 'growfund_create_pledge';
    const READ   = 'growfund_read_pledges';
    const EDIT   = 'growfund_edit_pledge';
    const DELETE = 'growfund_delete_pledge';

    protected $campaign_service;
    protected $pledge_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
        $this->pledge_service   = new PledgeService();
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
            case Backer::ROLE:
                return $this->backer_capabilities();
            default:
                return [];
        }
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args): array
    {
        $capability_map = [
            static::READ   => [$this, 'can_read'],
            static::CREATE => [$this, 'can_create'],
            static::EDIT   => [$this, 'can_edit'],
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

    protected function backer_capabilities()
    {
        return [
            static::READ,
        ];
    }

    protected function can_read(int $user_id, $pledge_id = null): array
    {
        if (empty($pledge_id)) {
            return [static::READ];
        }

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_id        = $pledge->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $pledge->backer->id === $user_id) {
            return ['exist'];
        }

        if ((int) $campaign_author_id === $user_id || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_create(int $user_id, int $campaign_id): array
    {
        $is_creator = $this->campaign_service->get_author_id($campaign_id) === $user_id;

        if ($is_creator || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_edit(int $user_id, int $pledge_id): array
    {
        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_id        = $pledge->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $pledge->backer->id === $user_id) {
            return ['exist'];
        }

        if ((int) $campaign_author_id === $user_id || $this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, $pledge_id = null): array
    {
        if (empty($pledge_id)) {
            return [static::DELETE];
        }

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_id        = $pledge->campaign->id;
        $campaign_author_id = $this->campaign_service->get_author_id($campaign_id);

        if ((int) $campaign_author_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
