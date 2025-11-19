<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Status\PledgeStatus;
use Growfund\Mailer;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;
use Growfund\Supports\PostMeta;
use InvalidArgumentException;

class PledgeCancelledMail extends Mailer
{
    public function with($data)
    {

        if (!isset($data['pledge_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        if (!isset($data['receiver_user_id']) && empty($data['receiver_user_id'])) {
            throw new InvalidArgumentException(esc_html__('Receiver User ID is required', 'growfund'));
        }

        if (!isset($data['pledge_cancelled_date'])) {
            throw new InvalidArgumentException(esc_html__('Pledge cancelled date is required', 'growfund'));
        }

        if (! isset($data['content_key'])) {
            throw new InvalidArgumentException(esc_html__('Content key is required', 'growfund'));
        }

        $user = growfund_user($data['receiver_user_id']);

        if (! $user->is_backer() && ! $user->is_fundraiser()) {
            $this->ignore_mail();
        }

        $this->using($data['content_key']);
        $this->set_receiver_user_id($user->get_id());
        $this->to($user->get_email());

        $pledge_dto = (new PledgeService())->get_by_id($data['pledge_id'])->get_values();

        if ($pledge_dto->status !== PledgeStatus::CANCELLED) {
            $this->ignore_mail();
        }

        $payment = $pledge_dto->payment->get_values();

        $pledge = [
            'campaign_title' => $pledge_dto->campaign->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $pledge_dto->campaign->id)),
            'reward_title' => $pledge_dto->reward->title,
            'pledge_amount' => $payment->total,
            'payment_method' => $payment->payment_method->label ?? '',
            'shipping_destination' => Arr::make($pledge_dto->backer->shipping_address ?? [])->filter(function ($value) {
                return !empty($value);
            })->join(', '),
            'estimated_delivery' => Date::format(PostMeta::get($pledge_dto->reward->id, 'estimated_delivery_date'), DateTimeFormats::HUMAN_READABLE_DATE),
            'pledge_id' => $pledge_dto->id,
            'cancelled_date' => Date::format($data['pledge_cancelled_date'], DateTimeFormats::HUMAN_READABLE_DATE),
            'backer_name' => sprintf('%s %s', $pledge_dto->backer->first_name, $pledge_dto->backer->last_name),
        ];

        return parent::with([
            'pledge_cancelled_card' => growfund_renderer()->get_html('mails.components.pledge-cancelled-card', [
                'pledge' => $pledge
            ]),
            'backer_name' => $pledge['backer_name'],
            'campaign_title' => $pledge['campaign_title'],
            'campaign_url' => $pledge['campaign_url'],
            'pledge_amount' => $pledge['pledge_amount'],
            'payment_method' => $pledge['payment_method'],
            'reward_title' => $pledge['reward_title'],
            'shipping_destination' => $pledge['shipping_destination'],
            'estimated_delivery' => $pledge['estimated_delivery'],
            'pledge_id' => $pledge['pledge_id'],
            'cancelled_date' => $pledge['cancelled_date'],
            'campaign_link_button' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('Pledge Again', 'growfund'),
                'link' => $pledge['campaign_url'],
                'colors' => $this->get_colors(),
            ]),
        ]);
    }
}
