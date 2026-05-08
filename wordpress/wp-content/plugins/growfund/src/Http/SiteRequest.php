<?php

namespace Growfund\Http;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Sanitizer;
use Growfund\Supports\Arr;
use Growfund\Supports\FileHandler;

class SiteRequest implements Request
{
    protected $request_nonce;
    protected $nonce_action;
    protected $attributes = [];
    protected $headers = [];

    public function __construct($request_nonce = null, $nonce_action = null)
    {
        $this->request_nonce = $request_nonce;
        $this->nonce_action = $nonce_action;
    }

    public function __get(string $name)
    {
        return $this->input($name);
    }

    public function __set(string $name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function set_attributes(array $attributes) {
        $this->attributes = $attributes;
    }

    public static function instance($request_nonce = null, $nonce_action = null)
    {
        return new static($request_nonce, $nonce_action);
    }

    public function get_method()
    {
        return wp_unslash(growfund_input_server('REQUEST_METHOD', ''));
    }

    public function get_route()
    {
        return wp_unslash(growfund_input_server('REQUEST_URI', ''));
    }

    public function get_headers()
    {
        if (empty($this->headers)) {
            $this->headers = array_change_key_case(getallheaders());
        }

        return $this->headers;
    }

    public function all()
    {
        return $this->attributes;
    }

    public function has(string $key)
    {
        $value = $this->input($key, null);

        return isset($value);
    }

    public function except(array $attributes)
    {
        return array_diff_key($this->all(), array_flip($attributes));
    }

    protected function maybe_request_contains_files() {
        $headers = $this->get_headers();

        return isset($headers['content-type']) && strpos($headers['content-type'], 'multipart/form-data') !== false;
    }

    public function input(string $key, $type = null)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        $input = null;

        if ($type !== Sanitizer::FILE) {
            $input = growfund_input_get($key, null, $type) ?? growfund_input_post($key, null, $type);
        }

        /**
         * @todo: need to improve input file if needed for wildcard keys with '.' and '*' when working on site.
         */
        if ($this->maybe_request_contains_files()) {
            if (is_null($input)) {
                $input = $this->get_file($key);
            }

            if ($type === Sanitizer::ARRAY && is_array($input)) {
                $file = $this->get_file($key) ?? [];

                $input = array_merge($input, $file);
            }

            if ($input === '') {
                $input = null;
            }
        }

        $this->attributes[$key] = $input;

        return $this->attributes[$key];
    }

    public function only(array $keys)
    {
        $is_associative = Arr::isAssociative($keys);
        $key_collection = Arr::make($keys);
        $attribute_keys = $is_associative ? array_keys($keys) : $keys;
        $attributes = [];

        foreach ($attribute_keys as $attribute_key) {
            if (strpos($attribute_key, '.') === false && !$this->is_array_attribute($key_collection, $attribute_key, $is_associative)) {
                $attributes[$attribute_key] = $this->input($attribute_key);
                continue;
			}

            $key_parts = explode('.', $attribute_key);
            $attribute_key = $key_parts[0];

			if (isset($attributes[$attribute_key]) && !empty($attributes[$attribute_key])) {
				continue;
			}

            $attributes[$attribute_key] = $this->input($attribute_key, Sanitizer::ARRAY);
        }
        
        return $attributes;
    }

    protected function is_array_attribute(Arr $key_collection, string $current_attribute_name, bool $is_associative) {
        $is_array = $key_collection->some(function ($key_value, $key_index) use ($current_attribute_name, $is_associative) {
			if ($is_associative) {
				if ($key_index === $current_attribute_name) {
					if (is_string($key_value) && strpos($key_value, 'array') !== false) {
						return true;
					}

					if (is_array($key_value)) {
						return Arr::make($key_value)->some(function ($value) {
							if (is_string($value) && strpos($value, 'array') !== false) {
								return true;
							}

							return false;
						});
					}
				}
                    
				if (strpos($key_index, '.') !== false) {
					$parts = explode('.', $key_index);

					if ($parts[0] === $current_attribute_name) {
						return true;
					}
				}

				return false;
			}

			if (strpos($key_value, '.') !== false) {
				$parts = explode('.', $key_value);

				if ($parts[0] === $current_attribute_name) {
					return true;
				}
			}

            return false;
		});

        return $is_array;
    }

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
     * @return array|null
     */
    public function get_file(string $key)
    {   
        if (
            !wp_verify_nonce(
                $this->request_nonce ?? wp_unslash(growfund_input_post('_wpnonce') ?? growfund_input_get('_wpnonce') ?? ''), 
                $this->nonce_action  ?? growfund_with_prefix('site_nonce')
            )
        ) {
            return null;
        }

        $files = FileHandler::format_files_form_request($_FILES);

        if (!isset($files[$key])) {
            return null;
        }

        return $files[$key];
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
