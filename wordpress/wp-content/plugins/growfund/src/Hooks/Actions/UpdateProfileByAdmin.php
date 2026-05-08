<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;

class UpdateProfileByAdmin extends UpdateUserByAdmin
{
    public function get_name()
    {
        return HookNames::WP_UPDATE_PROFILE;
    }
}
