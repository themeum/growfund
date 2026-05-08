<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Exceptions\AuthorizationException;
use Growfund\PostTypes\Reward;

/**
 * @method void authorize_create(int $campaign_id, int|null $user_id = null)
 * @method void authorize_update(int $reward_id, int|null $user_id = null)
 * @method void authorize_delete(int $reward_id = null, int|null $user_id = null)
 * @method void authorize_list(int $campaign_id, int|null $user_id = null)
 */
class RewardPolicy extends BasePolicy
{
    public function create(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $reward_id, $user_id = null)
    {
        $reward = get_post($reward_id);
        $parent_id = (int) $reward->post_parent;

        if ($reward->post_type !== Reward::NAME || !growfund_user($user_id)->can(CampaignCapabilities::EDIT, $parent_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $reward_id, $user_id = null)
    {
        $reward = get_post($reward_id);
        $parent_id = (int) $reward->post_parent;

        if ($reward->post_type !== Reward::NAME || !growfund_user($user_id)->can(CampaignCapabilities::EDIT, $parent_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function list(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
