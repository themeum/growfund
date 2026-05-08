<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Collaborator;
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
            case Collaborator::ROLE:
                return $this->collaborator_capabilities();
            case Backer::ROLE:
                return $this->backer_capabilities();
            default:
                return [];
        }
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args)
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

    protected function collaborator_capabilities()
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

    protected function can_read(int $user_id, $pledge_id = null)
    {
        if (empty($pledge_id)) {
            return [static::READ];
        }

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_id        = (int) $pledge->campaign->id;
        $campaign_author_id = (int) $pledge->campaign->author->id ?? 0;
        $campaign_fundraiser_id = (int) $pledge->campaign->fundraiser->id ?? 0;

        if ((int) $pledge->backer->id === $user_id) {
            return ['exist'];
        }

        if (
            $campaign_author_id === $user_id 
            || $campaign_fundraiser_id === $user_id 
            || $this->campaign_service->is_collaborator($user_id, $campaign_id)
        ) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_create(int $user_id, int $campaign_id)
    {
        $is_creator = $this->campaign_service->get_author_id($campaign_id) === $user_id;

        if ($is_creator) {
            return ['exist'];
        }

        $is_fundraiser = $this->campaign_service->get_fundraiser_id($campaign_id) === $user_id;

        if ($is_fundraiser) {
            return ['exist'];
        }

        if ($this->campaign_service->is_collaborator($user_id, $campaign_id)) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_edit(int $user_id, int $pledge_id)
    {
        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_id        = (int) $pledge->campaign->id;
        $campaign_author_id = (int) $pledge->campaign->author->id ?? 0;
        $campaign_fundraiser_id = (int) $pledge->campaign->fundraiser->id ?? 0;

        if ((int) $pledge->backer->id === $user_id) {
            return ['exist'];
        }

        if (
            $campaign_author_id === $user_id 
            || $campaign_fundraiser_id === $user_id 
            || $this->campaign_service->is_collaborator($user_id, $campaign_id)
        ) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, $pledge_id = null)
    {
        if (empty($pledge_id)) {
            return [static::DELETE];
        }

        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (!$pledge) {
            return ['do_not_allow'];
        }

        $campaign_author_id = (int) $pledge->campaign->author->id ?? 0;
        $campaign_fundraiser_id = (int) $pledge->campaign->fundraiser->id ?? 0;

        if ($campaign_author_id === $user_id) {
            return ['exist'];
        }

        if ($campaign_fundraiser_id === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
