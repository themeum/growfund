<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\FundCapabilities;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Core\AppSettings;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int $fund_id, int|null $user_id = null)
 * @method void authorize_get_by_id(int $fund_id, int|null $user_id = null)
 * @method void authorize_details(int $fund_id, int|null $user_id = null)
 * @method void authorize_delete(int $fund_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int[] $ids, int|null $user_id = null)
 */
class FundPolicy extends BasePolicy
{
    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create($user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::CREATE) || !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $fund_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::EDIT, $fund_id) || !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function get_by_id(int $fund_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function details(int $fund_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $fund_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::DELETE, $fund_id) || !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function empty_trash($user_id = null)
    {
        if (!growfund_user($user_id)->can(FundCapabilities::DELETE) || !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function bulk_actions(array $ids, $user_id = null)
    {
        foreach ($ids as $id) {
            if (!growfund_user($user_id)->can(FundCapabilities::DELETE, $id) || !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
                throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
            }
        }
    }
}
