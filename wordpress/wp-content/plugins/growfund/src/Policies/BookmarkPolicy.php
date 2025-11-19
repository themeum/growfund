<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\BookmarkCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_paginated(int $creator_id, int|null $user_id)
 * @method void authorize_remove(int $creator_id, int|null $user_id)
 */
class BookmarkPolicy extends BasePolicy
{
    public function paginated(int $creator_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BookmarkCapabilities::READ, $creator_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function remove(int $creator_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(BookmarkCapabilities::DELETE, $creator_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
