<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\DonorCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int $donor_id, int|null $user_id = null)
 * @method void authorize_overview(int $donor_id, int|null $user_id = null)
 * @method void authorize_donations(int $donor_id, int|null $user_id = null)
 * @method void authorize_delete(int $donor_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int|null $user_id = null)
 * @method void authorize_get_stats(int $donor_id, int|null $user_id = null)
 * @method void authorize_annual_receipts(int|null $user_id = null)
 */
class DonorPolicy extends BasePolicy
{
    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create($user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::CREATE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $donor_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::EDIT, $donor_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function overview(int $donor_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::READ, $donor_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function donations(int $donor_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::READ, $donor_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $donor_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonorCapabilities::DELETE, $donor_id)) {
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

    public function get_stats(int $donor_id, $user_id = null)
    {
        $user = growfund_user($user_id);

        if ((!$user->is_donor() && !$user->is_fundraiser()) || !$user->can(DonorCapabilities::READ, $donor_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function annual_receipts($user_id = null)
    {
        $user = growfund_user($user_id);

        if ($user->is_backer()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
