<?php

namespace Growfund\Supports;

use Growfund\Sanitizer;

defined( 'ABSPATH' ) || exit;

class RequestInput {
    protected $method;
    protected $key;
    protected $default;
    protected $sanitizer_type;

    public function __construct($method, $key, $default = null, $sanitizer_type = Sanitizer::TEXT)
    {
        $this->method = $method;
        $this->key = $key;
        $this->default = $default;
        $this->sanitizer_type = $sanitizer_type;
    }

    public function get()
    {
        $value = $this->get_value();

        if (is_null($value)) {
            return $this->default;
        }

        return $value;
    }

    protected function get_value() {
        $key_parts = explode('.', $this->key);
        $root = $key_parts[0];
        $has_path = count($key_parts) > 1;

        if ($has_path || $this->sanitizer_type === Sanitizer::ARRAY) {
            return Sanitizer::apply_rule(filter_input($this->get_input_type(), $root, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY), Sanitizer::ARRAY);
        }

        $input = filter_input($this->get_input_type(), $this->key, FILTER_DEFAULT);

        return Sanitizer::apply_rule($this->prepare_input_value($input), $this->sanitizer_type);
    }

    protected function prepare_input_value($value) {
        if (is_null($value) || $value === '') {
            return null;
        }

        if (strtolower($value) === 'null') {
            return null;
        }

        if (strtolower($value) === 'true') {
            return true;
        }
        
        if (strtolower($value) === 'false') {
            return false;
        }

        return $value;

    }


    protected function get_input_type () {
		switch (strtolower ($this->method)) {
			case 'get':
				return INPUT_GET;
			case 'post':
				return INPUT_POST;
			case 'server':
                return INPUT_SERVER;
            default:
                return INPUT_GET;
		}
    }
}
