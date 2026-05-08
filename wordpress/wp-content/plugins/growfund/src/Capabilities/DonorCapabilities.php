<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Collaborator;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Supports\UserMeta;
use Growfund\Traits\HasConstants;

class DonorCapabilities implements Capability
{
    use HasConstants;

    const CREATE              = 'growfund_create_donor';
    const READ                = 'growfund_read_donors';
    const READ_OTHERS         = 'growfund_read_other_donors';
    const EDIT                = 'growfund_edit_donor';
    const DELETE              = 'growfund_delete_donor';

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
            case Collaborator::ROLE:
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

    protected function donor_capabilities()
    {
        return [
            static::READ,
            static::EDIT,
            static::DELETE,
        ];
    }

    protected function can_read(int $user_id, $donor_id = null): array
    {
        if (empty($donor_id)) {
            return [static::READ];
        }

        if ($user_id === $donor_id) {
            return ['exist'];
        }

        return [static::READ_OTHERS];
    }

    protected function can_edit(int $user_id, int $donor_id): array
    {
        if ($user_id === $donor_id) {
            return ['exist'];
        }

        $created_by = UserMeta::get($donor_id, 'created_by');

        if ((int) $created_by === $user_id) {
            return ['exist'];
        }


        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, int $donor_id): array
    {
        if ($user_id === $donor_id) {
            return ['exist'];
        }

        $created_by = UserMeta::get($donor_id, 'created_by');

        if ((int) $created_by === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
