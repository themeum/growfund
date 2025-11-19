<?php

namespace Growfund\Policies;

defined( 'ABSPATH' ) || exit;

use Growfund\Capabilities\DonationCapabilities;
use Growfund\Constants\Status\DonationStatus;
use Growfund\Exceptions\AuthorizationException;
use Growfund\Exceptions\InvalidValidationRuleException;
use Growfund\Services\DonationService;
use Growfund\Core\AppSettings;

/**
 * @method void authorize_paginated(int|null $user_id = null)
 * @method void authorize_get_by_id(int $donation_id, int|null $user_id = null)
 * @method void authorize_create(int $campaign_id, int|null $user_id = null)
 * @method void authorize_update(int $donation_id, int|null $user_id = null)
 * @method void authorize_update_status(int $donation_id, int|null $user_id = null)
 * @method void authorize_delete(int $donation_id, int|null $user_id = null)
 * @method void authorize_empty_trash(int|null $user_id = null)
 * @method void authorize_bulk_actions(string $action, int[] $donation_ids, int|null $user_id = null)
 */
class DonationPolicy extends BasePolicy
{
    protected $donation_service;

    public function __construct()
    {
        $this->donation_service = new DonationService();
    }

    public function paginated($user_id = null)
    {
        if (!growfund_user($user_id)->can(DonationCapabilities::READ)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function get_by_id(int $donation_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonationCapabilities::READ, $donation_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function create(int $campaign_id, $user_id = null)
    {
        if (!growfund_user($user_id)->can(DonationCapabilities::CREATE, $campaign_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update(int $donation_id, $user_id = null)
    {
        $donation = $this->donation_service->get_by_id($donation_id);

        if (DonationStatus::COMPLETED === $donation->status) {
            throw new InvalidValidationRuleException(esc_html__('Completed donations can not be updated', 'growfund'));
        }

        if (!growfund_user($user_id)->can(DonationCapabilities::CREATE, $donation_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function update_status(int $donation_id, $user_id = null)
    {
        $donation = $this->donation_service->get_by_id($donation_id);

        if (DonationStatus::COMPLETED === $donation->status) {
            throw new InvalidValidationRuleException(esc_html__('Completed donations can not be updated', 'growfund'));
        }

        if (!growfund_user($user_id)->can(DonationCapabilities::EDIT_STATUS, $donation_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function delete(int $donation_id, $user_id = null)
    {
        $donation = $this->donation_service->get_by_id($donation_id);

        if (DonationStatus::COMPLETED === $donation->status) {
            throw new InvalidValidationRuleException(esc_html__('Completed donations can not be deleted', 'growfund'));
        }

        if (!growfund_user($user_id)->can(DonationCapabilities::DELETE, $donation_id)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function empty_trash($user_id = null)
    {
        if (!growfund_user($user_id)->can(DonationCapabilities::DELETE)) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }
    }

    public function bulk_actions(string $action, array $donation_ids, $user_id = null)
    {
        if ($action === 'reassign_fund' && !growfund_settings(AppSettings::CAMPAIGNS)->allow_fund()) {
            throw new AuthorizationException(esc_html__('You do not have permission for this action', 'growfund'));
        }

        foreach ($donation_ids as $donation_id) {
            $this->authorize_delete($donation_id, $user_id = null);
        }
    }
}
