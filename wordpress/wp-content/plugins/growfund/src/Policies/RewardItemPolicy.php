<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_all(int $campaign_id, int|null $user_id = null)
 * @method void authorize_create(int $campaign_id, int|null $user_id = null)
 * @method void authorize_update(int $item_id, int|null $user_id = null)
 * @method void authorize_delete(int $item_id, int|null $user_id = null)
 */
class RewardItemPolicy extends BasePolicy
{
    public function all(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $item_id, $user_id = null)
    {
        $reward_item = get_post($item_id);
        $parent_id = (int) $reward_item->post_parent;

        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $parent_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $item_id, $user_id = null)
    {
        $reward_item = get_post($item_id);
        $parent_id = (int) $reward_item->post_parent;

        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $parent_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
