<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int $fundraiser_id, int|null $user_id = null)
 * @method void authorize_update_status(int $fundraiser_id, int|null $user_id = null)
 * @method void authorize_delete(int $fundraiser_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int|null $user_id = null)
 * @method void authorize_overview(int $fundraiser_id, int|null $user_id = null)
 */
class FundraiserPolicy extends BasePolicy
{
    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->is_fundraiser()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function update(int $fundraiser_id, $user_id = null)
    {

        if (empty($user_id)) {
            $user = growfund_user();
        } else {
            $user = growfund_user($user_id);
        }

        if (!($user->get_id() === $fundraiser_id && $user->is_fundraiser()) && !$user->is_admin()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update_status(int $fundraiser_id, $user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function delete(int $fundraiser_id, $user_id = null)
    {
        if (!($user_id === $fundraiser_id && growfund_user($user_id)->is_fundraiser()) && !growfund_user($user_id)->is_admin()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function empty_trash($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function bulk_actions($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function overview(int $fundraiser_id, $user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }
}
