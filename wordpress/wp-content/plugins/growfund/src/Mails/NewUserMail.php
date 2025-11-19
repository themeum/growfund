<?php

namespace Growfund\Mails;

defined( 'ABSPATH' ) || exit;

use Growfund\Mailer;
use InvalidArgumentException;

class NewUserMail extends Mailer
{
    public function with($data)
    {
        if (!isset($data['user_id'])) {
            throw new InvalidArgumentException(esc_html__('User ID is required', 'growfund'));
        }

        if (!isset($data['receiver_user_id']) && empty($data['receiver_user_id'])) {
            throw new InvalidArgumentException(esc_html__('Receiver User ID is required', 'growfund'));
        }

        if (!isset($data['content_key'])) {
            throw new InvalidArgumentException(esc_html__('Content key is required', 'growfund'));
        }

        $user = growfund_user($data['user_id']);
        $mail_receiver = growfund_user($data['receiver_user_id']);

        if (!$user || !$mail_receiver) {
            $this->ignore_mail();
        }

        $this->set_receiver_user_id($data['receiver_user_id']);
        $this->using($data['content_key']);
        $this->to($mail_receiver->get_email());

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
