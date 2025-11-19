<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\AppConfigKeys;
use Growfund\Constants\OptionKeys;
use Growfund\Supports\AdminUser;
use Growfund\Supports\Arr;
use Growfund\Supports\MediaAttachment;

/**
 * Service class to handle WordPress option operations.
 *
 * Provides static methods for getting, setting, updating, and deleting options.
 *
 * @since 1.0.0
 */
class Option
{
    /**
     * Retrieve a value from the WordPress options table.
     *
     * @param string $name     The name of the option to retrieve.
     * @param mixed  $default  Optional. Default value to return if the option does not exist. * Default false.
     *
     * @return mixed The value of the option, or default if not found.
     */
    public static function get(string $name, $default = false)
    {
        $option = get_option($name, $default);

        switch ($name) {
            case OptionKeys::ECARD_TEMPLATE:
            case OptionKeys::EMAIL_NOTIFICATION_TEMPLATE:
                return static::get_option_with_media_image($option);
            case OptionKeys::PDF_PLEDGE_RECEIPT_TEMPLATE:
            case OptionKeys::PDF_DONATION_RECEIPT_TEMPLATE:
            case OptionKeys::PDF_ANNUAL_RECEIPT_TEMPLATE:
                return static::get_option_with_signature(
                    static::get_option_with_media_image($option)
                );
            case AppConfigKeys::EMAIL_AND_NOTIFICATIONS:
                return static::get_email_and_notifications($option);
            case AppConfigKeys::PAYMENT:
                return static::process_payment_settings($option);
            default:
                return empty($option) ? $default : $option;
        }
    }

    protected static function process_payment_settings($option)
    {
        if (empty($option['payments'])) {
            return $option;
        }

        $payments = Arr::make($option['payments'] ?? []);
        $payments = $payments->map(function ($payment) {
            if ($payment['type'] === 'manual-payment' && isset($payment['config']['logo'])) {
                $payment['config']['logo'] = MediaAttachment::make($payment['config']['logo']);
            }

            return $payment;
        });

        $option['payments'] = $payments->toArray();
        return $option;
    }

    protected static function get_email_and_notifications($option)
    {
        $default_mail_config = [
            'from_email' => $option['mail']['from_email'] ?? AdminUser::get_email(),
            'from_name' => $option['mail']['from_name'] ?? get_bloginfo('name'),
            'mailer' => $option['mail']['mailer'] ?? null,
        ];

        $mail_config = array_merge($default_mail_config, $option['mail'] ?? []);

        return array_merge($option ?? [], ['mail' => $mail_config]);
    }

    protected static function get_option_with_media_image($option)
    {
        if (!empty($option) && isset($option['media']['image'])) {
            $image = MediaAttachment::make($option['media']['image']);
            $option['media']['image'] = $image;
        }
        return $option;
    }

    protected static function get_option_with_signature($option)
    {
        if (!empty($option) && isset($option['content']) && isset($option['content']['signature']) && isset($option['content']['signature']['image'])) {
            $image = MediaAttachment::make($option['content']['signature']['image']);
            $option['content']['signature']['image'] = $image;
        }
        return $option;
    }

    /**
     * Set the value of a WordPress option (alias of update).
     *
     * @param string $name   The name of the option to set.
     * @param mixed  $value  The value to set.
     *
     * @return bool True if option value has changed, false if not or if update failed.
     */
    public static function set(string $name, $value)
    {
        return update_option($name, $value);
    }

    /**
     * Add a new option to the WordPress options table.
     *
     * @param string $name   The name of the option to add.
     * @param mixed  $value  The value to add.
     *
     * @return bool True if option value has changed, false if not or if update failed.
     */
    public static function add(string $name, $value)
    {
        return add_option($name, $value);
    }

    /**
     * Update the value of a WordPress option.
     *
     * @param string $name   The name of the option to update.
     * @param mixed  $value  The new value.
     *
     * @return bool True if the value was updated, false otherwise.
     */
    public static function update(string $name, $value)
    {
        switch ($name) {
            case OptionKeys::PDF_PLEDGE_RECEIPT_TEMPLATE:
            case OptionKeys::PDF_DONATION_RECEIPT_TEMPLATE:
            case OptionKeys::PDF_ANNUAL_RECEIPT_TEMPLATE:
                $current_value = get_option($name, []);
                $value['short_codes'] = $current_value['short_codes'] ?? [];
                break;
        }

        return update_option($name, $value);
    }

    protected static function prepare_email_contents_for_store($name, $value)
    {
        $email_contents = get_option($name, []);

        return array_merge($email_contents, $value);
    }

    /**
     * Delete an option from the WordPress options table.
     *
     * @param string $name  The name of the option to delete.
     *
     * @return bool True if the option was deleted, false otherwise.
     */
    public static function delete(string $name)
    {
        return delete_option($name);
    }

    public static function get_default_settings($key = null)
    {
        $default_settings_path = GROWFUND_DIR_PATH . 'resources/data/default-options.json';

        if (!file_exists($default_settings_path)) {
            return false;
        }

        $default_settings = json_decode(file_get_contents($default_settings_path), true);

        if (!is_null($key)) {
            return $default_settings[$key] ?? [];
        }

        return $default_settings;
    }

    /**
     * Store the default settings on after successful onboarding.
     *
     * @return bool
     */
    public static function store_default_settings(array $data = [])
    {
        $default_settings = static::get_default_settings();

        if (empty($default_settings)) {
            return false;
        }

        $app_config_keys = AppConfigKeys::all();
        $data_keys = !empty($data) ? array_keys($data) : [];

        foreach ($app_config_keys as $key) {
            $default_values = $default_settings[$key] ?? [];
            $data_values = in_array($key, $data_keys, true) ? $data[$key] : null;

            if (in_array($key, $data_keys, true)) {
                $default_values = is_array($data_values)
                    ? array_merge($default_values, $data_values ?? [])
                    : $data_values;
            }

            static::update($key, $default_values);
        }

        return true;
    }

    /**
     * Delete all the settings from the WordPress options table.
     *
     * @return bool True if the settings were deleted, false otherwise.
     */
    public static function delete_all_settings(array $keys = [])
    {
        $app_config_keys = AppConfigKeys::all();

        if (!empty($keys)) {
            $app_config_keys = array_values(
                array_intersect($keys, $app_config_keys)
            );
        }

        foreach ($app_config_keys as $key) {
            static::delete($key);
        }

        return true;
    }

    /**
     * Store the default email template content to the options table
     * 
     * @param string $key
     *
     * @return bool
     */
    public static function store_default_email_templates(string $key = '')
    {
        $default_email_templates_path = GROWFUND_DIR_PATH . 'resources/data/default-email-contents.json';

        if (!file_exists($default_email_templates_path)) {
            return false;
        }

        $contents = json_decode(file_get_contents($default_email_templates_path), true);

        if (empty($contents)) {
            return false;
        }

        if (!empty($key)) {
            return static::update($key, $contents[$key]);
        }

        foreach ($contents as $content_key => $content) {
            static::update($content_key, $content);
        }

        return true;
    }

    public static function get_default_pdf_templates($key = null)
    {
        $default_settings_path = GROWFUND_DIR_PATH . 'resources/data/default-pdf-contents.json';

        if (!file_exists($default_settings_path)) {
            return false;
        }

        $default_settings = json_decode(file_get_contents($default_settings_path), true);

        if (!is_null($key)) {
            return $default_settings[$key] ?? [];
        }

        return $default_settings;
    }

    /**
     * Store the default pdf template content to the options table
     * 
     * @param string $key
     *
     * @return bool
     */
    public static function store_default_pdf_templates(string $key = '')
    {
        $contents = static::get_default_pdf_templates();

        if (empty($contents)) {
            return false;
        }

        if (!empty($key)) {
            return update_option($key, $contents[$key]);
        }

        foreach ($contents as $content_key => $content) {
            update_option($content_key, $content);
        }

        return true;
    }
}
