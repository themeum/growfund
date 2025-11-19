<?php

namespace Growfund;

defined( 'ABSPATH' ) || exit;

use Growfund\Core\AppSettings;
use Growfund\Core\MailConfig;
use Growfund\Parsers\MailParser;
use Exception;
use InvalidArgumentException;

class Mailer
{
    protected $to;
    protected $subject;
    protected $heading;
    protected $message;
    protected $headers = [];
    protected $attachments = [];
    protected $variables = [];
    protected $is_plain_text = false;
    protected $content_key = null;
    protected $default_variables = [];
    protected $ignore_mail = false;
    protected $receiver_user_id = null;

    /**
     * Mail configuration instance
     *
     * @var MailConfig
     */
    protected $config;

    /**
     * Mail content parser instance
     *
     * @var MailParser
     */
    protected $parser;

    /**
     * Constructor.
     */
    public function __construct(MailConfig $config, MailParser $parser)
    {
        $this->config = $config;
        $this->parser = $parser;

        $this->register_default_variables();
    }

    /**
     * Register default variables.
     *
     * @return void
     */
    protected function register_default_variables()
    {
        $s = growfund_settings(AppSettings::GENERAL);
        $this->default_variables = [
            'site_name' => get_bloginfo('name'),
            'site_url' => site_url(),
            'year' => wp_date('Y'),
            'support_email' => growfund_settings(AppSettings::GENERAL)->get_organization_contact_email(),
            'privacy_policy' => growfund_renderer()->get_html('mails.components.links.privacy-policy'),
            'terms_and_conditions' => growfund_renderer()->get_html('mails.components.links.terms-and-conditions'),
            'organization_name' => growfund_settings(AppSettings::GENERAL)->get_organization_name(),
            'organization_location' => growfund_settings(AppSettings::GENERAL)->get_organization_location(),
        ];
    }

    /**
     * Set the mail content key.
     *
     * @param string $content_key
     * @return self
     */
    public function using(string $content_key)
    {
        $this->content_key = $content_key;

        return $this;
    }

    /**
     * Set the email heading.
     *
     * @param string $heading
     * @return $this
     */
    public function heading($heading)
    {
        $this->heading = $heading;
        return $this;
    }

    /**
     * Get email template colors color.
     *
     * @return array colors
     */
    public function get_colors()
    {
        $template = $this->config->get_template();

        return $template['colors'] ?? [];
    }

    /**
     * Set the email message.
     *
     * @param string $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Set the email recipient.
     *
     * @param string $email
     * @return $this
     */
    public function to($email)
    {
        $this->to = $email;
        return $this;
    }

    /**
     * Set the email subject.
     *
     * @param string $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->subject = $this->parser
            ->with($this->variables)
            ->parse($subject);
        return $this;
    }

    /**
     * Set the email variables.
     *
     * @param array $variables
     * @return $this
     */
    public function with(array $variables)
    {
        $this->variables = array_merge($this->default_variables, $variables);
        return $this;
    }

    /**
     * Attach a file to the email.
     *
     * @param string $path
     * @return $this
     */
    public function attach($path)
    {
        $this->attachments[] = $path;
        return $this;
    }

    /**
     * Add a header to the email.
     *
     * @param string $header
     * @return $this
     */
    public function header($header)
    {
        $this->headers[] = $header;
        return $this;
    }

    /**
     * Set the email to plain text.
     *
     * @return $this
     */
    public function plain_text()
    {
        $this->is_plain_text = true;

        return $this;
    }

    /**
     * Set the email to HTML.
     *
     * @return $this
     */
    public function html()
    {
        $this->is_plain_text = false;

        return $this;
    }

    /**
     * Ignore the email.
     *
     * @param bool $value
     * @return $this
     */
    public function ignore_mail(bool $value = true)
    {
        $this->ignore_mail = $value;
        return $this;
    }

    public function set_receiver_user_id($user_id)
    {
        $this->receiver_user_id = $user_id;
        return $this;
    }

    protected function is_enabled()
    {
        $user_notification_settings = [];

        if (!empty($this->receiver_user_id)) {
            $user = growfund_user($this->receiver_user_id);

            if (! $user->is_admin()) {
                $user_notification_settings = $user->get_meta('notification_settings', []);
            }
        }

        return $this->config->is_enabled($this->content_key, $user_notification_settings);
    }

    /**
     * Send the email.
     *
     * @return bool
     */
    public function send()
    {
        if ($this->ignore_mail) {
            return;
        }

        if (empty($this->content_key)) {
            throw new Exception(esc_html__('The content key is required to fetch the mail contents.', 'growfund'));
        }

        /**
         * Check if the email is enabled to send first.
         */
        if (!$this->is_enabled($this->content_key)) {
            return;
        }

        if ($this->is_plain_text) {
            $this->header('Content-Type: text/plain');
        } else {
            $this->header('Content-Type: text/html; charset=UTF-8');
        }

        if (!empty($this->content_key)) {
            $base_data = $this->config->content($this->content_key);

            if (!empty($base_data['subject'])) {
                $this->subject($this->subject ?? $base_data['subject']);
            }

            if (!empty($base_data['message'])) {
                $this->message($this->message ?? $base_data['message']);
            }

            if (!empty($base_data['heading'])) {
                $this->heading($this->heading ?? $base_data['heading']);
            }
        }

        if (empty($this->to)) {
            throw new InvalidArgumentException(esc_html__('Recipient email (to) is required.', 'growfund'));
        }

        if (empty($this->subject)) {
            throw new InvalidArgumentException(esc_html__('Email subject is required.', 'growfund'));
        }

        if (empty($this->body())) {
            throw new InvalidArgumentException(esc_html__('Email body content is empty.', 'growfund'));
        }

        return wp_mail(
            $this->to,
            $this->subject,
            $this->body(),
            $this->headers,
            $this->attachments
        );
    }

    /**
     * Get the email view.
     *
     * @return string
     */
    protected function view()
    {
        return 'mails.base_layout';
    }

    /**
     * Get the email body.
     *
     * @return string
     */
    protected function body()
    {
        return $this->parser
            ->with($this->variables)
            ->parse(
                growfund_renderer()->get_html($this->view(), [
                    'message' => $this->message,
                    'heading' => $this->heading,
                    'template' => $this->config->get_template(),
                ])
            );
    }
}
