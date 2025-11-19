<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Mailer;
use Growfund\Services\CampaignService;
use Growfund\Supports\CampaignGoal;
use Growfund\Supports\Date;
use InvalidArgumentException;

class CampaignFundedMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['campaign_id'])) {
            throw new InvalidArgumentException(esc_html__('Campaign ID is required', 'growfund'));
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

        $campaign_dto = (new CampaignService())->get_by_id($data['campaign_id'])->get_values();

        $campaign = [
            'campaign_title' => $campaign_dto->title,
            'campaign_creator' => $campaign_dto->created_by,
            'campaign_start_date' => Date::format($campaign_dto->start_date, DateTimeFormats::HUMAN_READABLE_DATE),
            'funded_percent' => CampaignGoal::goal_achieved_percentage($campaign_dto),
            'campaign_goal' => CampaignGoal::prepare_goal_for_display($campaign_dto->goal_type, $campaign_dto->goal_amount),
            'image' => $campaign_dto->images[0]['url'] ?? growfund_placeholder_image_url(),
            'campaign_url' => growfund_campaign_url($campaign_dto->slug),
            'backers_count' => $campaign_dto->number_of_contributors,
            'donors_count' => $campaign_dto->number_of_contributors,
            'fund_raised' => $campaign_dto->fund_raised,
        ];

        return parent::with(
            array_merge(
                [
                    'campaign_funded_card' => growfund_renderer()->get_html('mails.components.campaign-funded-card', ['campaign' => $campaign]),
                    'campaign_link_button' => growfund_renderer()->get_html('mails.components.link-button', [
                        'text' => __('Update Status', 'growfund'),
                        'link' => $campaign['campaign_url'],
                        'colors' => $this->get_colors(),
                    ]),
                    'campaign_title' => $campaign['campaign_title'],
                    'campaign_url' => $campaign['campaign_url'],
                    'backers_count' => $campaign['backers_count'],
                    'donors_count' => $campaign['donors_count'],
                    'fund_raised' => $campaign['fund_raised'],
                    'campaign_goal' => $campaign['campaign_goal'],
                ],
                $user->is_admin()
                    ? ['admin_name' => $user->get_display_name()]
                    : ['fundraiser_name' => $user->get_display_name()]
            )
        );
    }
}
