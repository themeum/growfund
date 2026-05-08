<?php

namespace Growfund\Mails\Backer;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Campaign\AppreciationType;
use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Constants\Pledge\PledgeOption;
use Growfund\Mailer;
use Growfund\Services\PledgeService;
use Growfund\Services\CampaignService;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use InvalidArgumentException;

class PledgePaidWithGivingThanksMail extends Mailer
{
    protected $content_key = MailKeys::BACKER_PLEDGE_PAID_WITH_GIVING_THANKS;

    public function with($data)
    {
        if (!isset($data['pledge_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        $pledge_service = new PledgeService();

        $pledge_dto = $pledge_service->get_by_id($data['pledge_id'])->get_values();
        $campaign_dto = (new CampaignService())->get_by_id($pledge_dto->campaign->id)->get_values();
        $payment = $pledge_dto->payment->get_values();

        if (
            empty($pledge_dto->backer->email)
            || $pledge_dto->pledge_option !== PledgeOption::WITHOUT_REWARDS
            || $campaign_dto->appreciation_type !== AppreciationType::GIVING_THANKS
        ) {
            $this->ignore_mail();
        }

        $this->set_receiver_user_id($pledge_dto->backer->id);
        $this->to($pledge_dto->backer->email);

        $pledge = [
            'campaign_title' => $campaign_dto->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $campaign_dto->id)),
            'pledge_amount' => $payment->total,
            'payment_method' => $payment->payment_method->label ?? '',
            'pledge_id' => $pledge_dto->id,
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
            'pledge_summary_card' => growfund_renderer()->get_html('mails.components.pledge-summary-without-reward-card', [
                'pledge' => $pledge
            ]),
            'pledge_amount_card' => growfund_renderer()->get_html('mails.components.backer.pledge-amount-card', [
                'pledge' => $pledge
            ]),
            'giving_thanks' => growfund_renderer()->get_html('mails.components.backer.giving-thanks', [
                'message' => $pledge_service->get_appreciation_message($campaign_dto, $pledge['pledge_amount'])
            ]),
            'alert_card' => !empty($campaign['campaign_end_date']) ? growfund_renderer()->get_html('mails.components.backer.pledge-alert-card', [
                'campaign' => $campaign
            ]) : '',
            'campaign_card' => growfund_renderer()->get_html('mails.components.campaign-card', [
                'campaign' => $campaign
            ]),
            'campaign_title' => $campaign['campaign_title'],
            'campaign_url' => $campaign['campaign_url'],
            'campaign_creator' => $campaign['campaign_creator'],
            'backer_count' => $campaign['backer_count'],
            'funded_percent' => $campaign['funded_percent'],
            'campaign_goal' => $campaign['campaign_goal'],
            'campaign_start_date' => $campaign['campaign_start_date'],
            'campaign_end_date' => $campaign['campaign_end_date'],
            'backer_name' => sprintf('%s %s', $pledge_dto->backer->first_name, $pledge_dto->backer->last_name),
            'pledge_amount' => $pledge['pledge_amount'],
            'payment_method' => $pledge['payment_method'],
            'pledge_id' => $pledge['pledge_id'],
            'campaign_link_button' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('See Campaign', 'growfund'),
                'link' => $campaign['campaign_url'],
                'colors' => $this->get_colors(),
            ]),
        ]);
    }
}
