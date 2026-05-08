<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

class AdminUser
{
    /**
     * @var static
     */
    protected static $instance;

    /**
     * @var \WP_User|null
     */
    protected $admin;

    public function __construct() {
        $admin_email = get_option('admin_email');

        $this->admin = get_user_by('email', $admin_email);
    }

    public static function make_instance() {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
    /**
     * Get admin user
     * 
     * @return \WP_User|null
     */
    public function get()
    {
        if (!$this->admin) {
            return null;
        }

        return $this->admin;
    }

    /**
     * Get admin user id
     * 
     * @return int
     */
    public static function get_id()
    {
        $instance = static::make_instance();

        return $instance->get()->ID ?? 0;
    }

    public static function get_email()
    {
        $instance = static::make_instance();

        return $instance->admin->user_email ?? '';
    }
}
