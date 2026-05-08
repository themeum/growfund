<?php

namespace Growfund\Mails\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Constants\Pledge\PledgeOption;
use Growfund\Mailer;
use Growfund\Services\PledgeService;
use Growfund\Services\CampaignService;
use Growfund\Supports\Arr;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use Growfund\Supports\PostMeta;
use InvalidArgumentException;

class OfflinePledgeRequestMail extends Mailer
{
    protected $content_key = MailKeys::BACKER_OFFLINE_PLEDGE_REQUEST;

    public function with($data)
    {
        if (!isset($data['pledge_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        $pledge_dto = (new PledgeService())->get_by_id($data['pledge_id'])->get_values();
        $campaign_dto = (new CampaignService())->get_by_id($pledge_dto->campaign->id)->get_values();
        $payment = $pledge_dto->payment->get_values();

        if (empty($pledge_dto->backer->email)) {
            $this->ignore_mail();
        }

        $this->set_receiver_user_id($pledge_dto->backer->id);
        $this->to($pledge_dto->backer->email);

        $pledge = [
            'campaign_title' => $campaign_dto->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $campaign_dto->id)),
            'reward_title' => $pledge_dto->reward->title,
            'pledge_amount' => $payment->total,
            'payment_method' => $payment->payment_method->label ?? '',
            'shipping_destination' => Arr::make($pledge_dto->backer->shipping_address ?? [])->filter(function ($value) {
                return !empty($value);
            })->join(', '),
            'estimated_delivery' => Date::format(PostMeta::get($pledge_dto->reward->id, 'estimated_delivery_date'), DateTimeFormats::HUMAN_READABLE_DATE),
            'pledge_id' => $pledge_dto->id,
            'payment_instructions' => $payment->payment_method->instruction,
        ];

        $campaign = [
            'campaign_title' => $campaign_dto->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $campaign_dto->id)),
            'campaign_creator' => $campaign_dto->author->display_name ?? '',
            'campaign_start_date' => Date::format($campaign_dto->start_date, DateTimeFormats::HUMAN_READABLE_DATE),
            'campaign_end_date' => Date::format($campaign_dto->end_date, DateTimeFormats::HUMAN_READABLE_DATE),
            'funded_percent' => CampaignGoal::goal_achieved_percentage($campaign_dto),
            'campaign_goal' => CampaignGoal::prepare_goal_for_display($campaign_dto->goal_type, $campaign_dto->goal_amount),
            'image' => $campaign_dto->images[0]['url'] ?? growfund_placeholder_image_url(),
            'fund_raised' => $campaign_dto->fund_raised,
            'backer_count' => $campaign_dto->number_of_contributors,
        ];


        return parent::with([
            'pledge_summary_card' => growfund_renderer()->get_html(
                $pledge_dto->pledge_option === PledgeOption::WITH_REWARDS
                    ? 'mails.components.pledge-summary-card'
                    : 'mails.components.pledge-summary-without-reward-card',
                [
                    'pledge' => $pledge
                ]
            ),
            'campaign_title' => $campaign['campaign_title'],
            'campaign_url' => $campaign['campaign_url'],
            'campaign_creator' => $campaign['campaign_creator'],
            'backer_count' => $campaign['backer_count'],
            'backer_name' => sprintf('%s %s', $pledge_dto->backer->first_name, $pledge_dto->backer->last_name),
            'pledge_amount' => $pledge['pledge_amount'],
            'payment_method' => $pledge['payment_method'],
            'reward_title' => $pledge['reward_title'],
            'shipping_destination' => $pledge['shipping_destination'],
            'estimated_delivery' => $pledge['estimated_delivery'],
            'pledge_id' => $pledge['pledge_id'],
        ]);
    }
}
