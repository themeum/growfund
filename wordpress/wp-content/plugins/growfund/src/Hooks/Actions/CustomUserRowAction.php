<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\Status\FundraiserStatus;
use Growfund\Constants\UserCustomRowAction;
use Growfund\Constants\UserTypes\Backer;
use Growfund\Constants\UserTypes\Donor;
use Growfund\Constants\UserTypes\Fundraiser;
use Growfund\Hooks\BaseHook;
use Growfund\Sanitizer;
use Growfund\Supports\Date;
use Growfund\Supports\UserMeta; 

class CustomUserRowAction extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $action = growfund_input_get('action', null, Sanitizer::TEXT);

        if (empty($action)) {
            return;
        }

        $user_id = growfund_input_get('user_id', 0, Sanitizer::INT);

        if (!$user_id || !current_user_can('edit_user', $user_id)) {
            return;
        }

        switch ($action) {
            case UserCustomRowAction::GROWFUND_MAKE_FUNDRAISER:
                if (check_admin_referer(sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_FUNDRAISER, $user_id)) !== 1) {
                    return;
                }

                $this->make_fundraiser($user_id);
                growfund_redirect(admin_url('users.php'));
                break;
            case UserCustomRowAction::GROWFUND_MAKE_DONOR:
                if (check_admin_referer(sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_DONOR, $user_id)) !== 1) {
                    return;
                }

                $this->make_donor($user_id);
                growfund_redirect(admin_url('users.php'));
                break;
            case UserCustomRowAction::GROWFUND_MAKE_BACKER:
                if (check_admin_referer(sprintf('%s_%s', UserCustomRowAction::GROWFUND_MAKE_BACKER, $user_id)) !== 1) {
                    return;
                }

                $this->make_baker($user_id);

                growfund_redirect(admin_url('users.php'));
                break;
            default:
                break;
        }
    }

    protected function make_fundraiser(int $user_id) {
        $user = growfund_user($user_id);

        if ($user->is_fundraiser()) {
            return;
        }

        $user->add_new_role(Fundraiser::ROLE);
        UserMeta::update($user_id, 'joined_at', Date::current_sql_safe());
        UserMeta::update($user_id, 'status', FundraiserStatus::ACTIVE );
    }

    protected function make_baker(int $user_id) {
        $user = growfund_user($user_id);

        if ($user->is_backer()) {
            return;
        }

        $user->add_new_role(Backer::ROLE);
    }

    protected function make_donor(int $user_id) {
        $user = growfund_user($user_id);

        if ($user->is_donor()) {
            return;
        }

        $user->add_new_role(Donor::ROLE);
    }
}
