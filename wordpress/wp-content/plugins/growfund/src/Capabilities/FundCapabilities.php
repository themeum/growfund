<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Services\FundService;
use Growfund\Traits\HasConstants;

class FundCapabilities implements Capability
{
    use HasConstants;

    const CREATE        = 'growfund_create_fund';
    const READ          = 'growfund_read_funds';
    const EDIT          = 'growfund_edit_fund';
    const DELETE        = 'growfund_delete_fund';

    protected $fund_service;

    public function __construct()
    {
        $this->fund_service = new FundService();
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
            default:
                return [];
        }
    }

    public function filter_capability(array $caps, string $cap, int $user_id, array $args): array
    {
        $capability_map = [
            static::EDIT        => [$this, 'can_edit'],
            static::DELETE      => [$this, 'can_delete'],
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

    protected function can_edit(int $user_id, int $fund_id): array
    {
        $fund = $this->fund_service->get_by_id($fund_id);

        if (!$fund) {
            return ['do_not_allow'];
        }

        if ((int) $fund->created_by === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }

    protected function can_delete(int $user_id, int $fund_id): array
    {
        if (empty($fund_id)) {
            return [static::DELETE];
        }

        $fund = $this->fund_service->get_by_id($fund_id);

        if (!$fund) {
            return ['do_not_allow'];
        }

        if ((int) $fund->created_by === $user_id) {
            return ['exist'];
        }

        return ['do_not_allow'];
    }
}
