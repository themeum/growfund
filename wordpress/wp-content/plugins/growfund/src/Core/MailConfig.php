<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Mail\MailKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Constants\WP;
use Growfund\QueryBuilder;
use Growfund\Supports\Option;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;

class MailConfig
{
    protected $email_and_notifications = null;
    protected $contents = [];
    protected $template = null;

    public function __construct()
    {
        $this->load_email_and_notification_settings();
        $this->load_email_contents();
        $this->load_email_template();
    }

    public function is_enabled(string $key, $user_notifications = [])
    {
        if (!is_array($user_notifications)) {
            $user_notifications = [];
        }
        
        $notifications = array_merge($this->email_and_notifications ?? [], $user_notifications);

        return !empty($notifications[$this->enabled_key($key)]);
    }

    public function content(string $key)
    {
        return $this->contents[$this->content_key($key)] ?? [];
    }

    public function get_template()
    {
        return $this->template;
    }

    public function get_content_keys()
    {
        return Arr::make(MailKeys::get_constant_values())->map(function ($key) {
            return $this->content_key($key);
        })->toArray();
    }

    protected function content_key(string $key)
    {
        return growfund_with_prefix($key);
    }

    protected function enabled_key(string $key)
    {
        return sprintf('is_enabled_%s', $key);
    }

    protected function load_email_and_notification_settings()
    {
        $email_and_notifications = growfund_settings(AppSettings::NOTIFICATIONS)->get();
        $email_and_notifications['is_enabled_email_verification'] = true;

        if (is_null($email_and_notifications)) {
            return $this;
        }

        if (isset($email_and_notifications['mail'])) {
            unset($email_and_notifications['mail']);
        }

        $this->email_and_notifications = $email_and_notifications;

        return $this;
    }

    protected function load_email_contents()
    {
        $keys = $this->get_content_keys();

        $content = QueryBuilder::query()
            ->table(WP::OPTIONS_TABLE)
            ->select(['option_name', 'option_value'])
            ->where_in('option_name', $keys)
            ->get();

        if (empty($content)) {
            return $this;
        }

        $content = Arr::make($content)->reduce(function ($result, $current) {
            $value = maybe_unserialize($current->option_value);
            $result[$current->option_name] = $value;

            return $result;
        }, [])->toArray();

        $this->contents = Arr::make($keys)->reduce(function ($result, $key) use ($content) {
            $result[$key] = $content[$key] ?? [];
            return $result;
        }, [])->toArray();

        return $this;
    }

    protected function load_email_template()
    {
        $template = Option::get(OptionKeys::EMAIL_NOTIFICATION_TEMPLATE, null);

        if (empty($template)) {
            return $this;
        }

        $template = is_string($template) ? json_decode($template, true) : $template;

        if (!empty($template['media']) && !empty($template['media']['image'])) {
            $attachment = MediaAttachment::make($template['media']['image']['id']);
            $template['media']['image'] = $attachment['url'] ?? growfund_placeholder_image_url();
        }

        $this->template = $template;

        return $this;
    }
}
