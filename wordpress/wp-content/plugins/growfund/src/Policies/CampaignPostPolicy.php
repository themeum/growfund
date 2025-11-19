<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Exceptions\AuthorizationException;

/**
 * @method void authorize_create(int $campaign_id, int|null $user_id = null)
 */
class CampaignPostPolicy extends BasePolicy
{
    public function create(int $campaign_id, $user_id = null)
    {

        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
