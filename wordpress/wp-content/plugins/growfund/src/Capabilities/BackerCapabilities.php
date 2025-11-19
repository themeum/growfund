<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Supports\UserMeta;
use Growfund\Traits\HasConstants;

class BackerCapabilities implements Capability
{
    use HasConstants;

    const CREATE              = 'growfund_create_backer';
    const READ                = 'growfund_read_backers';
    const READ_OTHERS         = 'growfund_read_other_backers';
    const EDIT                = 'growfund_edit_backer';
    const DELETE              = 'growfund_delete_backer';

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
            static::READ                => [$this, 'can_read'],
            static::EDIT                => [$this, 'can_edit'],
            static::DELETE              => [$this, 'can_delete'],
        ];

        if (isset($capability_map[$cap])) {
            return call_user_func_array($capability_map[$cap], array_merge([$user_id], $args));
        }

        return $caps;
    }

    protected function fundraiser_capabilities()
    {
        return [
            static::READ,
            static::READ_OTHERS,
            static::CREATE,
        ];
    }

    protected function backer_capabilities()
    {
        return [
            static::READ,
            static::EDIT,
            static::DELETE,
        ];
    }

    protected function can_read(int $user_id, $backer_id = null): array
    {
        if (empty($backer_id)) {
            return [static::READ];
        }

        if ($user_id === $backer_id) {
            return ['exist'];
        }

        return [static::READ_OTHERS];
    }

    protected function can_edit(int $user_id, int $backer_id): array
    {
        if ($user_id === $backer_id) {
            return ['exist'];
        }

        $created_by = UserMeta::get($backer_id, 'created_by');

        if ((int) $created_by === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, int $backer_id): array
    {
        if ($user_id === $backer_id) {
            return ['exist'];
        }

        $created_by = UserMeta::get($backer_id, 'created_by');

        if ((int) $created_by === $user_id) {
            return ['exist'];
        }


        return ['do_not_allow'];
    }
}
