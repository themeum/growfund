<?php

namespace Growfund\Hooks\Actions;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\HookNames;
use Growfund\Constants\HookTypes;
use Growfund\Core\AppSettings;
use Growfund\Hooks\BaseHook;
use Growfund\Supports\Option;
use PHPMailer\PHPMailer\PHPMailer;

class SMTPConfig extends BaseHook
{
    public function get_name()
    {
        return HookNames::WP_PHP_MAILER_INIT;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            return;
        }

        $php_mailer = $args[0];

        if (!$php_mailer instanceof PHPMailer) {
            return;
        }

        $options = growfund_settings(AppSettings::NOTIFICATIONS)->get();

        if (empty($options)) {
            return;
        }

        $mail_config = $options['mail'] ?? null;

        if (empty($mail_config)) {
            return;
        }

        $is_smtp = $mail_config['mailer'] === 'smtp';

        if ($is_smtp) {
            $php_mailer->isSMTP();
            $php_mailer->Host       = $mail_config['host'] ?? '';
            $php_mailer->SMTPAuth   = !empty($mail_config['enable_authentication']);
            $php_mailer->Port       = (int) ($mail_config['port'] ?? 25);
            $php_mailer->Username   = $mail_config['username'] ?? '';
            $php_mailer->Password   = $mail_config['password'] ?? '';
            $php_mailer->SMTPSecure = $mail_config['encryption'] ?? 'tls';
            $php_mailer->FromName   = $mail_config['from_name'];
            $php_mailer->From       = $mail_config['from_email'];
        }
    }
}
