<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

class FlashMessage
{
    const COOKIE_NAME = 'growfund_flash';
    const COOKIE_EXPIRY = 60; // seconds

    protected static $current_messages = [];

    /**
     * Set a flash message in cookie.
     *
     * @param string $key
     * @param string|array $message
     * @param string $type
     */
    public static function set($key, $message)
    {
        $messages = static::get_all();
        $messages[$key] = $message;

        static::store($messages);
    }

    /**
     * Get and remove a flash message from cookie.
     *
     * @param string $key
     * @return array|null
     */
    public static function get($key)
    {
        if (isset(static::$current_messages[$key])) {
            return static::$current_messages[$key];
        }

        $messages = static::get_all();

        if (!isset($messages[$key])) {
            return null;
        }

        $message = $messages[$key];
        unset($messages[$key]);

        static::store($messages);

        return $message;
    }

    /**
     * Check if a message exists.
     *
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        $messages = static::get_all();
        return isset($messages[$key]);
    }

    /**
     * Clear all messages.
     */
    public static function clear_all()
    {
        static::store([]);
    }

    /**
     * Get all flash messages from cookie.
     *
     * @return array
     */
    protected static function get_all() {
        if ( ! isset( $_COOKIE[ static::COOKIE_NAME ] ) ) {
            return [];
        }

        $sanitized = sanitize_text_field( wp_unslash( $_COOKIE[ static::COOKIE_NAME ]));
        $data = json_decode( $sanitized, true );

        return is_array( $data ) ? $data : [];
    }

    /**
     * Store messages to cookie.
     *
     * @param array $messages
     */
    protected static function store(array $messages)
    {
        $json = wp_json_encode($messages);
        setcookie(
            static::COOKIE_NAME,
            $json,
            time() + static::COOKIE_EXPIRY,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HttpOnly
        );

        $_COOKIE[static::COOKIE_NAME] = $json;
    }

    public static function flush()
    {
        if (headers_sent()) {
            return;
        }
        
        $messages = static::get_all();

        foreach ($messages as $key => $message) {
            static::$current_messages[$key] = $message;
        }

        setcookie(static::COOKIE_NAME, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    }
}
