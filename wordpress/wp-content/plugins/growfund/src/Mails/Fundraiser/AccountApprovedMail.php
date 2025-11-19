<?php

namespace Growfund\Mails\Fundraiser;

use Growfund\Constants\Mail\MailKeys;
use Growfund\Mailer;
use InvalidArgumentException;

class AccountApprovedMail extends Mailer
{
    protected $content_key = MailKeys::FUNDRAISER_ACCOUNT_APPROVED;

    public function with($data)
    {
        if (!isset($data['user_id'])) {
            throw new InvalidArgumentException(esc_html__('User ID is required', 'growfund'));
        }

        $user = growfund_user($data['user_id']);

        if (!$user->is_fundraiser()) {
            $this->ignore_mail();
        }

        $this->set_receiver_user_id($user->get_id());
        $this->to($user->get_email());

        $login_url = growfund_login_url();

        return parent::with([
            'user_name' => $user->get_username(),
            'user_email' => $user->get_email(),
            'first_name' => $user->get_first_name(),
            'last_name' => $user->get_last_name(),
            'user_table' => growfund_renderer()->get_html('mails.components.user-table', [
                'username' => $user->get_username(),
                'email' => $user->get_email(),
            ]),
            'login_button' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('Login', 'growfund'),
                'link' => $login_url,
                'colors' => $this->get_colors(),
            ]),
        ]);
    }
}
