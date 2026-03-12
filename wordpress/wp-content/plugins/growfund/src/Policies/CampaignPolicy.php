<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\CampaignCapabilities;
use Growfund\Constants\Status\CampaignStatus;
use Growfund\Core\AppSettings;
use Growfund\DTO\Campaign\UpdateCampaignDTO;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Exceptions\InvalidValidationRuleException;
use Growfund\Services\CampaignService;
use Growfund\Supports\Date;

/**
 * @method void authorize_overview(int $campaign_id, int|null $user_id = null)
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_get_by_id(int $campaign_id, int|null $user_id = null)
 * @method void authorize_create(int|null $user_id = null)
 * @method void authorize_update(int $campaign_id, object $payload, int|null $user_id = null)
 * @method void authorize_delete(int $campaign_id, int|null $user_id = null)
 * @method void authorize_bulk_actions(int[] $campaign_ids, string $action, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_update_status(int $campaign_id, string $status, int|null $user_id = null)
 * @method void authorize_admin_only(int|null $user_id = null)
 * @method void authorize_mark_as_ready_for_pickup(int $campaign_id, int|null $user_id = null)
 */
class CampaignPolicy extends BasePolicy
{
    /** @var CampaignService */
    protected $campaign_service;

    public function __construct()
    {
        $this->campaign_service = new CampaignService();
    }

    public function overview(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function get_by_id(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create($user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::CREATE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $campaign_id, UpdateCampaignDTO $payload, $user_id = null)
    {
        $campaign = $this->campaign_service->get_by_id($campaign_id);

        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        $is_interactive   = $this->campaign_service->is_campaign_interactive($campaign->id);
        $is_funded        = $campaign->status === CampaignStatus::FUNDED;
        $is_completed     = $campaign->status === CampaignStatus::COMPLETED;

        $requesting_status = $payload->status;

        if ($is_interactive || $is_funded || $is_completed) {
            if ($requesting_status === CampaignStatus::DRAFT) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. You are not able to make it draft.', 'growfund')
                );
            }

            if (!Date::is_same($campaign->start_date, $payload->start_date)) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. The launch date cannot be changed.', 'growfund')
                );
            }

            if ($campaign->appreciation_type !== $payload->appreciation_type) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. The appreciation type cannot be changed.', 'growfund')
                );
            }

            if ($campaign->appreciation_type === 'giving-thanks' && $this->is_giving_thanks_data_modified($campaign->giving_thanks, $payload->giving_thanks)) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. The thanks giving pledge ranges cannot be changed.', 'growfund')
                );
            }
        }
    }

    public function delete(int $campaign_id, $user_id = null)
    {
        $campaign = $this->campaign_service->get_by_id($campaign_id);

        if (!growfund_user($user_id)->can(CampaignCapabilities::DELETE, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        $is_interactive   = $this->campaign_service->is_campaign_interactive($campaign->id);
        $is_funded        = $campaign->status === CampaignStatus::FUNDED;
        $is_completed     = $campaign->status === CampaignStatus::COMPLETED;

        if ($is_interactive || $is_funded || $is_completed) {
            throw new InvalidValidationRuleException(
                esc_html__('The campaign has already been received contributions. You are not able to delete it.', 'growfund')
            );
        }
    }

    public function bulk_actions(array $campaign_ids, string $action, $user_id = null)
    {
        foreach ($campaign_ids as $campaign_id) {
            $campaign = $this->campaign_service->get_by_id($campaign_id);

            if ($action === 'trash') {
                $this->delete($campaign_id, $user_id);
                continue;
            }

            if ($action === 'delete') {
                $this->empty_trash($user_id);
                continue;
            }

            if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
                throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
            }

            $is_interactive   = $this->campaign_service->is_campaign_interactive($campaign->id);
            $is_funded        = $campaign->status === CampaignStatus::FUNDED;
            $is_completed     = $campaign->status === CampaignStatus::COMPLETED;

            if ($is_interactive || $is_funded || $is_completed) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. You are not able to perform this action.', 'growfund')
                );
            }
        }
    }

    public function empty_trash($user_id = null)
    {
        if (!growfund_user($user_id)->can(CampaignCapabilities::DELETE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        if (growfund_user($user_id)->is_fundraiser()) {
            if (!growfund_settings(AppSettings::PERMISSIONS)->fundraisers_can_delete_campaigns()) {
                throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
            }
        }
    }

    public function update_status(int $campaign_id, $status, $user_id = null)
    {
        $campaign = $this->campaign_service->get_by_id($campaign_id);

        if (!growfund_user($user_id)->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        if (growfund_user($user_id)->is_admin() && in_array($status, [CampaignStatus::COMPLETED, CampaignStatus::FUNDED, CampaignStatus::DECLINED], true)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        $is_interactive   = $this->campaign_service->is_campaign_interactive($campaign->id);
        $is_funded        = $campaign->status === CampaignStatus::FUNDED;
        $is_completed     = $campaign->status === CampaignStatus::COMPLETED;

        if ($is_interactive || $is_funded || $is_completed) {
            if ($status === CampaignStatus::DRAFT) {
                throw new InvalidValidationRuleException(
                    esc_html__('The campaign has already been received contributions. You are not able to make it draft.', 'growfund')
                );
            }
        }
    }

    /**
     * Authorize admin only activities.
     *
     * @param int|null $user_id
     * @return void
     */
    public function admin_only($user_id = null)
    {
        if (!growfund_user($user_id)->is_admin()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    /**
     * Check thanks giving messages are modified or not.
     * The constrains are:
     * 1. Any previous message cannot be removed.
     * 2. The from and to value of any item of the previous messages cannot be changed.
     * 3. The message of the previous items could be changed.
     * 4. Any new message can be added.
     *
     * @param array $previous
     * @param array $current
     * @return boolean
     */
    protected function is_giving_thanks_data_modified(array $previous, array $requesting)
    {
        // Any previous message cannot be removed.
        if (count($requesting) < count($previous)) {
            return true;
        }

        foreach ($previous as $index => $value) {
            $requesting_from = $requesting[$index]['pledge_from'];
            $requesting_to = $requesting[$index]['pledge_to'];
            $previous_from = $value['pledge_from'];
            $previous_to = $value['pledge_to'];

            if ($requesting_from !== $previous_from || $requesting_to !== $previous_to) {
                return true;
            }
        }

        return false;
    }

    public function mark_as_ready_for_pickup(int $campaign_id, ?int $user_id = null)
    {
        $campaign = $this->campaign_service->get_by_id($campaign_id);
        $user = growfund_user($user_id);

        if (!$user->can(CampaignCapabilities::EDIT, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        if (!$user->is_admin() || !$user->is_fundraiser()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        if ($campaign->status !== CampaignStatus::FUNDED || !$campaign->allow_local_pickup) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }
}
