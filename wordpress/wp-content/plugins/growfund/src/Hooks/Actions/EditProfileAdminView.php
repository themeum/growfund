<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;
use Growfund\Constants\HookNames;

class EditProfileAdminView extends EditUserAdminView
{
    public function get_name()
    {
        return HookNames::WP_EDIT_PROFILE_FORM;
    }
}
