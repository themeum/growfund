<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\BackerCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int $backer_id, int|null $user_id = null)
 * @method void authorize_campaigns_by_backer(int $backer_id, int|null $user_id = null)
 * @method void authorize_overview(int $backer_id, int|null $user_id = null)
 * @method void authorize_delete(int $backer_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int|null $user_id = null)
 * @method void authorize_giving_stats(int $backer_id, int|null $user_id = null)
 */
class BackerPolicy extends BasePolicy
{
    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create($user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::CREATE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::EDIT, $backer_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function campaigns_by_backer(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::READ, $backer_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function overview(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::READ, $backer_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::DELETE, $backer_id)) {
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

    public function giving_stats(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BackerCapabilities::READ, $backer_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
