<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Core\User;
use Growfund\Hooks\BaseHook;
use Growfund\Mails\EmailVerificationMail;
use Growfund\Mails\NewUserMail;
use Growfund\Sanitizer;
use Growfund\Supports\AdminUser;
use Growfund\Supports\User as UserSupport;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Utils;

class NewUserSave extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_USER_REGISTER;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $user_id = $args[0];

        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $action = growfund_input_post('action', null, Sanitizer::TEXT);
        $createuser = growfund_input_post('createuser', null, Sanitizer::TEXT);
        $username = growfund_input_post('user_login', null, Sanitizer::TEXT);
        $email = growfund_input_post('email', null, Sanitizer::TEXT);

        if ($action !== 'createuser' && !empty($createuser)) {
            return;
        }

        $user = growfund_user($user_id);

        if (empty($user->get()) || $user->get_username() !== $username || $user->get_email() !== $email) {
            return;
        }

        $shipping_address = growfund_input_post('shipping_address', null, Sanitizer::ARRAY);
        $billing_address = growfund_input_post('billing_address', null, Sanitizer::ARRAY);
        $is_billing_address_same = growfund_input_post('is_billing_address_same', false, Sanitizer::BOOL);
        $is_verified = growfund_input_post('is_verified', false, Sanitizer::BOOL);

        if ($is_billing_address_same) {
            $billing_address = $shipping_address;
        }

        UserMeta::update($user->get_id(), 'shipping_address', $shipping_address);
        UserMeta::update($user->get_id(), 'billing_address', $billing_address);
        UserMeta::update($user->get_id(), 'is_billing_address_same', $is_billing_address_same);
        UserMeta::update($user->get_id(), 'email_verified', $is_verified);

        $additional_roles = growfund_input_post('additional_roles', [], Sanitizer::ARRAY);
        $growfund_roles = Utils::get_growfund_roles();

        foreach ($additional_roles as $role) {
            $role = Sanitizer::apply_rule($role, Sanitizer::TEXT);

            if (isset(wp_roles()->roles[$role]) && in_array($role, $growfund_roles, true)) {
                $user->add_new_role($role);
            }
        }
    }
}
