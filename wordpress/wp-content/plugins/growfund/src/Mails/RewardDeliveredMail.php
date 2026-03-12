<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Mailer;
use Growfund\Services\PledgeService;
use Growfund\Supports\Date;
use InvalidArgumentException;

class RewardDeliveredMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['pledge_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        if (!isset($data['receiver_user_id']) && empty($data['receiver_user_id'])) {
            throw new InvalidArgumentException(esc_html__('Receiver User ID is required', 'growfund'));
        }

        if (!isset($data['reward_delivered_date'])) {
            throw new InvalidArgumentException(esc_html__('Reward delivered date is required', 'growfund'));
        }

        if (!isset($data['content_key'])) {
            throw new InvalidArgumentException(esc_html__('Content key is required', 'growfund'));
        }

        $receiver_user = growfund_user($data['receiver_user_id']);

        if (!$receiver_user) {
            $this->ignore_mail();
        }

        $this->using($data['content_key']);
        $this->set_receiver_user_id($receiver_user->get_id());
        $this->to($receiver_user->get_email());

        $pledge_dto = (new PledgeService())->get_by_id($data['pledge_id'])->get_values();
        $payment = $pledge_dto->payment->get_values();

        if (empty($pledge_dto->reward)) {
            $this->ignore_mail();
        }

        $pledge = [
            'campaign_title' => $pledge_dto->campaign->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $pledge_dto->campaign->id)),
            'reward_title' => $pledge_dto->reward->title,
            'pledge_amount' => $payment->total,
            'payment_method' => $payment->payment_method->label ?? '',
            'reward_delivered_date' => Date::format($data['reward_delivered_date'], DateTimeFormats::HUMAN_READABLE_DATE),
            'pledge_id' => $pledge_dto->id,
        ];

        return parent::with(
            array_merge(
                [
                    'reward_delivered_card' => growfund_renderer()->get_html('mails.components.reward-summary-card', [
                        'pledge' => $pledge
                    ]), /* @deprecated since 1.0.9: use reward_summary_card */
                    'reward_summary_card' => growfund_renderer()->get_html('mails.components.reward-summary-card', [
                        'pledge' => $pledge
                    ]),
                    'backer_name' => sprintf('%s %s', $pledge_dto->backer->first_name ?? '', $pledge_dto->backer->last_name ?? ''),
                    'campaign_title' => $pledge['campaign_title'],
                    'campaign_url' => $pledge['campaign_url'],
                    'pledge_amount' => $pledge['pledge_amount'],
                    'payment_method' => $pledge['payment_method'],
                    'reward_title' => $pledge['reward_title'],
                    'reward_delivered_date' => $pledge['reward_delivered_date'],
                    'pledge_id' => $pledge['pledge_id'],
                ],
                $receiver_user->is_admin() ? ['admin_name' => $receiver_user->get_display_name()] : [],
                $receiver_user->is_fundraiser() ? ['fundraiser_name' => $receiver_user->get_display_name()] : []
            )
        );
    }
}
