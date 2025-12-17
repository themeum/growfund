<?php

namespace Growfund\Http;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Sanitizer;

class SiteRequest implements Request
{
    protected $attributes = [];
    protected $headers = [];

    public function __get(string $name)
    {
        return $this->input($name);
    }

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public static function instance()
    {
        return new static();
    }

    public function get_method()
    {
        return Sanitizer::apply_rule(wp_unslash(growfund_input_server('REQUEST_METHOD')), Sanitizer::TEXT);
    }

    public function get_route()
    {
        return Sanitizer::apply_rule(wp_unslash(growfund_input_server('REQUEST_URI')), Sanitizer::TEXT);
    }

    public function get_headers()
    {
        if (empty($this->headers)) {
            $this->headers = getallheaders();
        }

        return $this->headers;
    }

    public function all()
    {
        $get_input = filter_input_array(INPUT_GET) ?? [];
        $post_input = filter_input_array(INPUT_POST) ?? [];
        return array_merge($get_input, $post_input);
    }

    public function has(string $key)
    {
        $inputs = $this->all();
        return isset($inputs[$key]);
    }

    public function except(array $attributes)
    {
        return array_diff_key($this->all(), array_flip($attributes));
    }

    public function only(string $key)
    {
        return $this->input($key);
    }

    public function input(string $key, $default = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        $this->attributes[$key] = growfund_input_get($key) ?? growfund_input_post($key) ?? $default;

        return $this->attributes[$key];
    }

    public function get(string $key, string $type, $default = null)
    {
        $value = $this->input($key, $default);

        if (empty($type)) {
            $type = Sanitizer::TEXT;
        }

        return Sanitizer::apply_rule($value, $type);
    }

    /**
     * Get a string value with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_string(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::TEXT, $default);
    }

    /**
     * Get a date value.
     *
     * @since 1.0.0
     *
     * @param string $key     The key to retrieve.
     * @param string|null  $default Default value if the key doesn't exist.
     * @return string
     */
    public function get_date(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::DATE, $default);
    }

    /**
     * Get a datetime value.
     *
     * @since 1.0.0
     *
     * @param string $key     The key to retrieve.
     * @param string|null  $default Default value if the key doesn't exist.
     * @return string
     */
    public function get_datetime(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::DATETIME, $default);
    }

    /**
     * Get a text with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_text(string $key, $default = null)
    {
        return $this->get_string($key, $default);
    }

    /**
     * Get a html supported content with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key     The key to retrieve.
     * @param string|null  $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_html(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::TEXTAREA, $default);
    }

    /**
     * Get a email with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_email(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::EMAIL, $default);
    }

    /**
     * Get a url with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_url(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::URL, $default);
    }

    /**
     * Get a key value with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_key(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::KEY, $default);
    }

    /**
     * Get a title value with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_title(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::TITLE, $default);
    }

    /**
     * Get a file name with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_file_name(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::TITLE, $default);
    }

    /**
     * Get mime type with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_mime_type(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::MIME_TYPE, $default);
    }

    /**
     * Get an integer value.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param int|null $default Default value if the key doesn't exist.
     * @return int|null
     */
    public function get_int(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::INT, $default);
    }

    /**
     * Get a boolean value.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param bool $default Default value if the key doesn't exist.
     * @return bool
     */
    public function get_bool(string $key, bool $default = false)
    {
        return $this->get($key, Sanitizer::BOOL, $default);
    }

    /**
     * Get a float value.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param float|null $default Default value if the key doesn't exist.
     * @return float|null
     */
    public function get_float(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::FLOAT, $default);
    }

    /**
     * Get a money value.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param float $default Default value if the key doesn't exist.
     * @return float
     */
    public function get_money(string $key, $default = 0)
    {
        return $this->get($key, Sanitizer::MONEY, $default);
    }

    /**
     * Get an array value.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param array|null $default Default value if the key doesn't exist.
     * @return array|null
     */
    public function get_array(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::ARRAY, $default);
    }
}
