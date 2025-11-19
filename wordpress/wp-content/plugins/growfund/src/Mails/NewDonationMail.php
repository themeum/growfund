<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Mailer;
use Growfund\Services\DonationService;
use Growfund\Supports\Date;
use Growfund\Core\AppSettings;
use InvalidArgumentException;

class NewDonationMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['donation_id'])) {
            throw new InvalidArgumentException(esc_html__('Pledge ID is required', 'growfund'));
        }

        if (!isset($data['receiver_user_id']) && empty($data['receiver_user_id'])) {
            throw new InvalidArgumentException(esc_html__('Receiver User ID is required', 'growfund'));
        }

        if (!isset($data['content_key'])) {
            throw new InvalidArgumentException(esc_html__('Content Key is required', 'growfund'));
        }

        $user = growfund_user($data['receiver_user_id']);

        if (!$user) {
            $this->ignore_mail();
        }

        $this->set_receiver_user_id($data['receiver_user_id']);
        $this->using($data['content_key']);
        $this->to($user->get_email());

        $donation_dto = (new DonationService())->get_by_id($data['donation_id'])->get_values();

        $donation = [
            'campaign_title' => $donation_dto->campaign->title,
            'campaign_url' => growfund_campaign_url(get_post_field('post_name', $donation_dto->campaign->id)),
            'donation_amount' => $donation_dto->amount,
            'payment_method' => $donation_dto->payment_method->label ?? '',
            'payment_id' => $donation_dto->id,
            'tribute' => sprintf('%s %s %s', $donation_dto->tribute_type, $donation_dto->tribute_salutation, $donation_dto->tribute_to),
            'donation_date' => Date::format($donation_dto->created_at, DateTimeFormats::HUMAN_READABLE_DATE),
            'donor_name' => sprintf('%s %s', $donation_dto->donor->first_name, $donation_dto->donor->last_name),
        ];

        return parent::with([
            'donation_card' => growfund_renderer()->get_html('mails.components.donation-card', [
                'donation' => $donation
            ]),
            'donation_amount' => $donation['donation_amount'],
            'tribute' => growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute()
                ? growfund_renderer()->get_html('mails.components.tribute', [
                    'tribute' => $donation['tribute']
                ])
                : '',
            'campaign_title' => $donation['campaign_title'],
            'campaign_url' => $donation['campaign_url'],
            'donation_date' => $donation['donation_date'],
            'payment_method' => $donation['payment_method'],
            'payment_id' => $donation['payment_id'],
            'donor_name' => $donation['donor_name'],
        ]);
    }
}
