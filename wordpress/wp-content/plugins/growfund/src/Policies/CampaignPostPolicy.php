<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Supports\PostMeta;

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

        $status = PostMeta::get($campaign_id, 'status');

        if (
            in_array(
                $status, 
                [
                    CampaignStatus::DRAFT, 
                    CampaignStatus::TRASHED, 
                    CampaignStatus::DECLINED, 
                    CampaignStatus::CANCELLED, 
                    CampaignStatus::PENDING
                ],
                true
            )
        ) {
            throw new AuthorizationException(esc_html__('You need to publish the campaign first', 'growfund'));
        }
    }
}
