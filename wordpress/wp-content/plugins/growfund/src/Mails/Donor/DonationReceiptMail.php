<?php

namespace Growfund\Mails\Donor;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\DateTimeFormats;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Core\AppSettings;
use Growfund\Mailer;
use Growfund\Services\DonationService;
use Growfund\Supports\Date;
use InvalidArgumentException;

class DonationReceiptMail extends Mailer
{
    protected $content_key = MailKeys::DONOR_DONATION_RECEIPT;

    public function with($data)
    {
        if (!isset($data['donation_id'])) {
            throw new InvalidArgumentException(esc_html__('Donation ID is required', 'growfund'));
        }

        $donation_dto = (new DonationService())->get_by_id($data['donation_id'])->get_values();

        if (empty($donation_dto->donor->email)) {
            return $this->ignore_mail();
        }

        $this->set_receiver_user_id($donation_dto->donor->id);
        $this->to($donation_dto->donor->email);

        $receipt_url = growfund_donation_receipt_download_url($donation_dto->uid);

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
            'donation_receipt_card' => growfund_renderer()->get_html('mails.components.donor.donation-receipt-card', [
                'donation' => $donation
            ]),
            'tribute' => growfund_settings(AppSettings::CAMPAIGNS)->allow_tribute()
                ? growfund_renderer()->get_html('mails.components.tribute', [
                    'tribute' => $donation['tribute']
                ])
                : '',
            'receipt_download_button' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('Download Receipt', 'growfund'),
                'link' => $receipt_url,
                'colors' => $this->get_colors(),
            ]),
            'donor_name' => $donation['donor_name'],
            'donation_amount' => $donation['donation_amount'],
            'campaign_title' => $donation['campaign_title'],
            'campaign_url' => $donation['campaign_url'],
            'donation_date' => $donation['donation_date'],
            'payment_method' => $donation['payment_method'],
            'payment_id' => $donation['payment_id'],
        ]);
    }
}
