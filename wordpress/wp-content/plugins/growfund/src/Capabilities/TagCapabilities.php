<?php

namespace Growfund\Capabilities;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Contracts\Capability;
use Growfund\Traits\HasConstants;

class TagCapabilities implements Capability
{
    use HasConstants;

    const CREATE = 'growfund_create_tag';

    protected $fund_service;

    public function handle()
    {
        // implement later if needed
    }

    public function get_capabilities($role = null)
    {
        switch ($role) {
            case Fundraiser::ROLE:
                return $this->fundraiser_capabilities();
            default:
                return static::get_constant_values();
        }
    }

    protected function fundraiser_capabilities()
    {
        return [
            static::CREATE,
        ];
    }
}
