<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Pledge\PledgeOption;
use Growfund\Mailer;
use Growfund\Services\PledgeService;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;
use Growfund\Supports\PostMeta;
use InvalidArgumentException;

class NewPledgeMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['pledge_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        if (!isset($data['receiver_user_id']) && empty($data['receiver_user_id'])) {
            throw new InvalidArgumentException(esc_html__('Receiver User ID is required', 'growfund'));
        }

        if (! isset($data['content_key'])) {
            throw new InvalidArgumentException(esc_html__('Content Key is required', 'growfund'));
        }

        $user = growfund_user($data['receiver_user_id']);

        if (! $user->is_admin() && ! $user->is_fundraiser()) {
            $this->ignore_mail();
        }

        $this->using($data['content_key']);
        $this->set_receiver_user_id($user->get_id());
        $this->to($user->get_email());

        $pledge_dto = (new PledgeService())->get_by_id($data['pledge_id'])->get_values();
        $payment = $pledge_dto->payment->get_values();

        $pledge = [
            'campaign_title' => $pledge_dto->campaign->title ?? '',
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $pledge_dto->campaign->id)),
            'reward_title' => $pledge_dto->reward->title,
            'pledge_amount' => $payment->total,
            'payment_method' => $payment->payment_method->label ?? '',
            'shipping_destination' => Arr::make($pledge_dto->backer->shipping_address ?? [])->filter(function ($value) {
                return !empty($value);
            })->join(', '),
            'estimated_delivery' => !empty($pledge_dto->reward) 
                ? Date::format(
                    PostMeta::get($pledge_dto->reward->id, 'estimated_delivery_date'), 
                    DateTimeFormats::HUMAN_READABLE_DATE
                ) 
                : '',
            'pledge_id' => $pledge_dto->id,
        ];

        return parent::with(
            array_merge(
                [
                    'pledge_summary_card' => growfund_renderer()->get_html(
                        $pledge_dto->pledge_option === PledgeOption::WITH_REWARDS
                            ? 'mails.components.pledge-summary-card'
                            : 'mails.components.pledge-summary-without-reward-card',
                        [
                            'pledge' => $pledge
                        ]
                    ),
                    'backer_name' => sprintf('%s %s', $pledge_dto->backer->first_name, $pledge_dto->backer->last_name),
                    'campaign_title' => $pledge['campaign_title'],
                    'campaign_url' => $pledge['campaign_url'],
                    'pledge_amount' => $pledge['pledge_amount'],
                    'payment_method' => $pledge['payment_method'],
                    'reward_title' => $pledge['reward_title'],
                    'shipping_destination' => $pledge['shipping_destination'],
                    'estimated_delivery' => $pledge['estimated_delivery'],
                    'pledge_id' => $pledge['pledge_id'],
                ],
                $user->is_admin()
                    ? ['admin_name' => $user->get_display_name()]
                    : ['fundraiser_name' => $user->get_display_name()]
            )
        );
    }
}
