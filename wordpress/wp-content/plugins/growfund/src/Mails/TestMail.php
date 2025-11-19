<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Mail\MailKeys;
use Growfund\Mailer;
use InvalidArgumentException;

class TestMail extends Mailer
{
    protected $content_key = MailKeys::FUNDRAISER_CAMPAIGN_FUNDED;

    public function with($data)
    {
        if (!isset($data['user_id'])) {
            throw new InvalidArgumentException(esc_html__('User ID is required', 'growfund'));
        }

        $user = growfund_user($data['user_id']);

        if (!$user) {
            throw new InvalidArgumentException(esc_html__('User not found', 'growfund'));
        }

        $campaign = [
            'title' => 'Doraemon SSD Card',
            'backers' => 500,
            'fund_raised' => '$32000.00',
        ];

        $this->to($user->get_email());

        return parent::with([
            'campaign_title' => $campaign['title'],
            'email' => $user->get_email(),
            'fundraiser_campaign_funded_card' => growfund_renderer()->get_html('mails.components.campaign-table', ['campaign' => $campaign]),
            'pledge_summary_card' => growfund_renderer()->get_html('mails.components.campaign-table', ['campaign' => $campaign]),
            'button_text' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('Update Status', 'growfund'),
                'link' => site_url('/'),
            ]),
            'support_email' => growfund_renderer()->get_html('mails.components.links.email', ['email' => 'support@cf.com']),
        ]);
    }
}
