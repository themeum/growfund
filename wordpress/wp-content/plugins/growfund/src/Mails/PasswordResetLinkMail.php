<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Mail\MailKeys;
use Growfund\Mailer;
use Growfund\Supports\UserMeta;
use InvalidArgumentException;

class PasswordResetLinkMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['user_id'])) {
            throw new InvalidArgumentException(esc_html__('User ID are required', 'growfund'));
        }

        $user = growfund_user($data['user_id']);

        if (!$user || empty($user->get_email())) {
            $this->ignore_mail();
        }

        $reset_key = UserMeta::get($user->get_id(), 'password_reset_key', true);
        $reset_link = growfund_reset_password_url() . '?key=' . $reset_key . '&login=' . rawurlencode($user->get_username());

        $this->set_receiver_user_id($user->get_id());
        $this->to($user->get_email());

        $data = [];

        if ($user->is_admin()) {
            $this->using(MailKeys::ADMIN_PASSWORD_RESET_REQUEST);
            $data = ['admin_name' => $user->get_email()];
        }

        if ($user->is_fundraiser()) {
            $this->using(MailKeys::FUNDRAISER_PASSWORD_RESET_REQUEST);
            $data = ['fundraiser_name' => $user->get_email()];
        }

        if ($user->is_donor()) {
            $this->using(MailKeys::DONOR_PASSWORD_RESET_REQUEST);
            $data = ['donor_name' => $user->get_email()];
        }

        if ($user->is_backer()) {
            $this->using(MailKeys::BACKER_PASSWORD_RESET_REQUEST);
            $data = ['backer_name' => $user->get_email()];
        }


        return parent::with(array_merge([
            'password_reset_table' => growfund_renderer()->get_html('mails.components.password-reset-table', [
                'username' => $user->get_username(),
            ]),
            'password_reset_button' => growfund_renderer()->get_html('mails.components.link-button', [
                'text' => __('Reset Password', 'growfund'),
                'link' => $reset_link,
                'colors' => $this->get_colors(),
            ]),
            'reset_password_url' => $reset_link,
        ], $data));
    }
}
