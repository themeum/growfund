<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\PledgeCapabilities;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Exceptions\InvalidValidationRuleException;
use Growfund\Services\CampaignService;
use Growfund\Services\PledgeService;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_backer_pledges(int $backer_id, int|null $user_id = null)
 * @method void authorize_get_by_id(int $pledge_id, int|null $user_id = null)
 * @method void authorize_create(int $campaign_id, int|null $user_id = null)
 * @method void authorize_update_status(int $pledge_id, int|null $user_id = null)
 * @method void authorize_delete(int $pledge_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(int[] $pledge_ids, int|null $user_id = null)
 */
class PledgePolicy extends BasePolicy
{
    protected $pledge_service;

    public function __construct()
    {
        $this->pledge_service = new PledgeService();
    }

    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(PledgeCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function backer_pledges(int $backer_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(PledgeCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function get_by_id(int $pledge_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(PledgeCapabilities::READ, $pledge_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(PledgeCapabilities::CREATE, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update_status(int $pledge_id, $user_id = null)
    {
        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (PledgeStatus::COMPLETED === $pledge->status || PledgeStatus::BACKED === $pledge->status) {
            throw new InvalidValidationRuleException(esc_html__('Completed pledges can not be updated', 'growfund'));
        }

        if (!growfund_user($user_id)->can(PledgeCapabilities::EDIT, $pledge_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $pledge_id, $user_id = null)
    {
        $pledge = $this->pledge_service->get_by_id($pledge_id);

        if (PledgeStatus::COMPLETED === $pledge->status || PledgeStatus::BACKED === $pledge->status) {
            throw new InvalidValidationRuleException(esc_html__('Completed pledges can not be deleted', 'growfund'));
        }

        if (!growfund_user($user_id)->can(PledgeCapabilities::DELETE, $pledge_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function empty_trash($user_id = null)
    {
        if (!growfund_user($user_id)->can(PledgeCapabilities::DELETE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function bulk_actions(array $pledge_ids, $user_id = null)
    {
        foreach ($pledge_ids as $pledge_id) {
            $this->authorize_delete($pledge_id, $user_id = null);
        }
    }
}
