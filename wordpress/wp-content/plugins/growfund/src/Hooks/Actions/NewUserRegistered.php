<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Constants\Mail\MailKeys;
use Growfund\Core\User;
use Growfund\Hooks\BaseHook;
use Growfund\Mails\EmailVerificationMail;
use Growfund\Mails\NewUserMail;
use Growfund\Supports\AdminUser;
use Growfund\Supports\User as UserSupport;

class NewUserRegistered extends BaseHook
{
    public function get_name()
    {
        return HookNames::USER_REGISTER;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $user_id = $args[0];

        $is_apply_email_verification = apply_filters(HookNames::GROWFUND_IS_APPLY_EMAIL_VERIFICATION, true); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound

        if (!$is_apply_email_verification) {
            return;
        }

        $user = growfund_user($user_id);

        if (!$user->is_fundraiser() && !$user->is_donor() && !$user->is_backer()) {
            return;
        }

        if (UserSupport::is_guest($user_id)) {
            return;
        }

        $token = UserSupport::generate_verification_token($user_id);

        $this->schedule_verification_emails($user_id, $token);
        $this->schedule_emails($user_id);

        if ($user->is_backer() || $user->is_donor()) {
            $this->schedule_user_emails($user);
        }
    }

    protected function schedule_verification_emails($user_id, $token)
    {
        growfund_scheduler()
            ->resolve(EmailVerificationMail::class)
            ->with([
				'user_id' => $user_id,
				'token' => $token
			])
            ->group('growfund_user_emails')
            ->schedule_email();
    }

    protected function schedule_user_emails(User $user)
    {
        growfund_scheduler()
            ->resolve(NewUserMail::class)
            ->with([
                'content_key' => $user->is_backer() ? MailKeys::BACKER_NEW_BACKER_REGISTRATION : MailKeys::DONOR_NEW_DONOR_REGISTRATION,
                'user_id' => $user->get_id(),
                'receiver_user_id' => $user->get_id(),
            ])
            ->group('growfund_user_emails')
            ->schedule_email();
    }

    protected function schedule_emails($user_id)
    {
        growfund_scheduler()
            ->resolve(NewUserMail::class)
            ->with([
                'content_key' => MailKeys::ADMIN_NEW_USER_REGISTRATION,
                'user_id' => $user_id,
                'receiver_user_id' => AdminUser::get_id(),
            ])
            ->group('growfund_user_emails')
            ->schedule_email();
    }
}
