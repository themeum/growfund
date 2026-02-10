<?php

namespace Growfund\Core;

defined( 'ABSPATH' ) || exit;

abstract class AppSettings
{
    /**
     * Settings values
     * @var array|null
     */
    protected $settings = null;

    /**
     * Available setting keys
     * @var string
     */
    const GENERAL = 'general';
    const PAGES = 'pages';
    const CAMPAIGNS = 'campaigns';
    const NOTIFICATIONS = 'notifications';
    const PAYMENT = 'payment';
    const PERMISSIONS = 'permissions';
    const RECEIPTS = 'receipts';
    const SECURITY = 'security';
    const ADVANCED = 'advanced';
    const BRANDING = 'branding';

    /**
     * Get settings values or a specific value by key
     *
     * @param string|null $key
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (empty($key)) {
            return $this->settings;
        }

        if (!strpos($key, '.')) {
            return $this->settings[$key] ?? $default;
        }

        $keys = explode('.', $key);

        return $this->recursively_get($this->settings, $keys, $default);
    }

    /**
     * Refresh settings values
     * @return static
     */
    abstract public function refresh();

    /**
     * Return the nested setting values by key recursively.
     *
     * @param array $settings
     * @param array $keys
     * @param mixed $default
     *
     * @return mixed|null
     */
    protected function recursively_get(array $settings, array $keys, $default = null)
    {
        if (empty($keys) || empty($settings)) {
            return $default;
        }

        $parent_key = $keys[0];
        $child_keys = array_slice($keys, 1);

        if (empty($child_keys)) {
            return $settings[$parent_key] ?? $default;
        }

        if (empty($settings[$parent_key])) {
            return $default;
        }

        return $this->recursively_get($settings[$parent_key], $child_keys, $default);
    }
}
