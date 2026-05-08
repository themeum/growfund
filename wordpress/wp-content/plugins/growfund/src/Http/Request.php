<?php

namespace Growfund\Http;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request as RequestContract;
use Growfund\Sanitizer;
use Growfund\Supports\Arr;
use Growfund\Supports\FileHandler;
use WP_REST_Request;

/**
 * Handles REST API request data abstraction for growfund operations.
 *
 * @since 1.0.0
 */
class Request implements RequestContract
{
    /**
     * The request's input attributes.
     *
     * @since 1.0.0
     * @var array
     */
    protected $attributes = [];

    /**
     * The HTTP method used for the request (e.g. GET, POST).
     *
     * @since 1.0.0
     * @var string
     */
    protected $method;

    /**
     * The route URI for the request.
     *
     * @since 1.0.0
     * @var string
     */
    protected $route;

    /**
     * The headers associated with the request.
     *
     * @since 1.0.0
     * @var array
     */
    protected $headers;

    /**
     * Magic getter to retrieve request attributes.
     *
     * @since 1.0.0
     *
     * @param string $name The name of the attribute.
     * @return mixed|null The attribute value or null if not set.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic setter to set request attributes.
     *
     * @since 1.0.0
     *
     * @param string $name  The name of the attribute.
     * @param mixed  $value The value to assign.
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Create a new Request instance from a WP_REST_Request.
     *
     * @since 1.0.0
     *
     * @param WP_REST_Request $request The WordPress REST request object.
     * @return self
     */
    public static function from_wp_rest_request(WP_REST_Request $request)
    {
        $instance = new static();

        $files = FileHandler::format_files_form_request($request->get_file_params());

        $attributes = array_merge_recursive(
            $instance->attributes, 
            $request->get_params(), 
            $files
        );

        $content_type = $request->get_content_type() ? $request->get_content_type()['value'] : null;

        if ($content_type === 'multipart/form-data') {
            $attributes = static::prepare_form_data_attributes($attributes);
        }

        $instance->attributes = $attributes;
        $instance->method = $request->get_method();
        $instance->route = $request->get_route();
        $instance->headers = $request->get_headers();

        return $instance;
    }

    protected static function prepare_form_data_attributes(array $attributes) {
        foreach ($attributes as $key => $value) {
            if ($value === '') {
                $attributes[$key] = null;
            }

            if (is_array($value)) {
                $attributes[$key] = static::prepare_form_data_attributes($value);
            }

            if (is_object($value)) {
                $attributes[$key] = static::prepare_form_data_attributes((array) $value);
            }
        }

        return $attributes;
    }

    /**
     * Get the HTTP method used in the request.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_method()
    {
        return $this->method;
    }

    /**
     * Get the route URI of the request.
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function get_route()
    {
        return $this->route;
    }

    /**
     * Get the headers associated with the request.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_headers()
    {
        return $this->headers;
    }

    /**
     * Get all input attributes.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function all()
    {
        if ($this->has('_method')) {
            $this->remove('_method');
        }

        return $this->attributes;
    }

    /**
     * Check if an attribute exists.
     *
     * @since 1.0.0
     *
     * @param string $key The key of the attribute.
     * @return bool
     */
    public function has(string $key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Remove an attribute.
     *
     * @since 1.0.0
     *
     * @param string $key The key of the attribute.
     * @return void
     */
    public function remove(string $key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Get all input attributes except the specified keys.
     *
     * @since 1.0.0
     *
     * @param array $attributes The attribute keys to exclude.
     * @return array
     */
    public function except(array $attributes)
    {
        return array_diff_key($this->attributes, array_flip($attributes));
    }


    /**
     * `input()` method.
     *
     * @since 1.0.0
     *
     * @param string $key The key of the attribute.
     * @param string|null $type type to cast the result to: int, float, bool, string, array with proper sanitization.
     * @return mixed|null
     */
    public function input(string $key, $type = null)
    {
        $input = $this->attributes[$key] ?? null;

        if (!is_null($type)) {
            return Sanitizer::apply_rule($input, $type);
        }

        return $input;
    }

    public function only(array $keys)
    {
        $keys = Arr::isAssociative($keys) ? array_keys($keys) : $keys;
        $keys = Arr::make($keys)->map(function ($value) {
            $key_part = explode('.', $value);

            return $key_part[0];
        })->unique()->toArray();
        
        return array_intersect_key($this->all(), array_flip($keys));
    }

    /**
     * Get a value from the request with optional default and type casting.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string $type Optional type to cast the result to: int, float, bool, string, array with proper sanitization.
     * @param mixed $default Default value if the key doesn't exist.
     * @return mixed|null
     */
    public function get(string $key, string $type, $default = null)
    {
        $value = $this->input($key) ?? $default;
        $type = $type ? $type : Sanitizer::TEXT;

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
     * Get a column value with sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @param array $whitelist Optional whitelist of allowed values.
     * @return string|null
     */
    public function get_column(string $key, $default = null, array $whitelist = [])
    {
        $value = $this->get($key, Sanitizer::COLUMN, $default);

        if (!empty($whitelist)) {
            return in_array($value, $whitelist, true) ? $value : $default;
        }

        return $value;
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
     * Get a text with textarea sanitization applied.
     *
     * @since 1.0.0
     *
     * @param string $key The key to retrieve.
     * @param string|null $default Default value if the key doesn't exist.
     * @return string|null
     */
    public function get_text(string $key, $default = null)
    {
        return $this->get($key, Sanitizer::TEXTAREA, $default);
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
        return $this->get($key, Sanitizer::RICH_TEXT, $default);
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
     * Get a file.
     *
     * @since 1.1.0
     *
     * @param string $key     The key to retrieve.
     * @return file|null
     */
    public function get_file(string $key)
    {
        // @todo: need to improve input file for wildcard keys with '.' and '*' . $attributes merge with $request->get_file_params(). so that its not working properly.

        return $this->get($key, Sanitizer::FILE);
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
     * Get a money for storage.
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
