<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\TagCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int|null $user_id)
 * @method void authorize_delete(int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int|null $user_id = null)
 */
class TagsPolicy extends BasePolicy
{
    public function create($user_id = null)
    {
        if (!growfund_user($user_id)->can(TagCapabilities::CREATE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function delete($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function empty_trash($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }

    public function bulk_actions($user_id = null)
    {
        throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
    }
}
