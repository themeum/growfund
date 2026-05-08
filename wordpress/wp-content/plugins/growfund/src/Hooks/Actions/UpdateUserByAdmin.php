<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Hooks\BaseHook;
use Growfund\Sanitizer;
use Growfund\Supports\UserMeta;
use Growfund\Supports\Utils;
use WP_User;

class UpdateUserByAdmin extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_UPDATE_USER;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            return;
        }

        $user_id = (int) $args[0];
        
        if (empty($user_id) || !current_user_can('edit_user', $user_id)) {
            return;
        }

        $user = new WP_User($user_id);

        if (empty($user->ID)) {
            return;
        }
        
        $shipping_address = growfund_input_post('shipping_address', null, Sanitizer::ARRAY);
        $billing_address = growfund_input_post('billing_address', null, Sanitizer::ARRAY);
        $is_billing_address_same = growfund_input_post('is_billing_address_same', false, Sanitizer::BOOL);
        $is_verified = growfund_input_post('is_verified', false, Sanitizer::BOOL);

        if ($is_billing_address_same) {
            $billing_address = $shipping_address;
        }

        UserMeta::update($user->ID, 'shipping_address', $shipping_address);
        UserMeta::update($user->ID, 'billing_address', $billing_address);
        UserMeta::update($user->ID, 'is_billing_address_same', $is_billing_address_same);
        UserMeta::update($user->ID, 'email_verified', $is_verified);

        if (!defined('IS_PROFILE_PAGE') || !IS_PROFILE_PAGE) {
            $additional_roles = growfund_input_post('additional_roles', [], Sanitizer::ARRAY);

            add_action('wp_update_user', function($updated_user_id) use ($user_id, $additional_roles) {
                if ((int) $updated_user_id === $user_id && !empty($additional_roles)) {
                    $user = new WP_User($user_id);

                    if (empty($user->ID)) {
                        return;
                    }

                    $growfund_roles = Utils::get_growfund_roles();

                    foreach ($additional_roles as $role) {
                        $role = Sanitizer::apply_rule($role, Sanitizer::TEXT);

                        if (isset(wp_roles()->roles[$role]) && in_array($role, $growfund_roles, true)) {
                            $user->add_role($role);
                        }
                    }
                }
            });
        }
    }
}
