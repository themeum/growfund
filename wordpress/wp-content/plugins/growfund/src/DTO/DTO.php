<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\CastAttribute;
use Growfund\Sanitizer;
use Exception;
use JsonSerializable;

/**
 * Base Data Transfer Object
 *
 * @since 1.0.0
 */
class DTO implements JsonSerializable
{
    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'description', 'slug'];


    /**
     * Fields that are considered to cast data when serialized.
     * 
     * @var array
     */
    protected $casts = [];

    /**
     * Tracks which attributes have been prepared for display
     *
     * @var bool
     */
    protected $prepared_for_display = false;


    /**
     * Fields to exclude from public attributes.
     *
     * @var array
     */
    protected $excluded_keys = [];

    /**
     * Fields to pick from public attributes.
     *
     * @var array
     */
    protected $picked_keys = [];

    /**
     * Dto constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function from_array(array $data)
    {
        return new static($data);
    }

    /**
     * Define the sanitization rules for the DTO.
     *
     * @return array
     */
    protected static function sanitization_rules()
    {
        return [];
    }

    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function from_array_with_sanitize(array $data)
    {
        if (empty(static::sanitization_rules())) {
            return new static($data);
        }

        $data = Sanitizer::make($data, static::sanitization_rules())->get_sanitized_data();

        return new static($data);
    }

    /**
     * Return an array representation of the object
     * 
     * @return array
     */
    public function jsonSerialize(): array
    {
        $this->cast_attributes();

        return $this->to_array();
    }

    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function to_array()
    {
        if (!empty($this->excluded_keys)) {
            return $this->except($this->excluded_keys);
        }

        if (!empty($this->picked_keys)) {
            return $this->only($this->picked_keys);
        }

        return $this->all();
    }

    /**
     * Extract metadata fields only
     *
     * @return array
     */
    public function get_meta(array $except = [])
    {
        $fields = $this->all();
        $meta = [];
        $fields_to_skip = array_merge(static::$base_fields, $except);

        foreach ($fields as $key => $value) {
            if (!in_array($key, $fields_to_skip, true)) {
                $meta[$key] = $value;
            }
        }

        return $meta;
    }

    /**
     * Get all fields
     *
     * @return array
     */
    public function all()
    {
        $data = $this->get_public_vars();

        return $data;
    }

    /**
     * get all excluded fields
     * @param array $keys
     * @return array
     */
    public function except(array $keys)
    {
        $data = $this->all();

        return array_diff_key($data, array_flip($keys));
    }

    /**
     * set all excluded fields
     * @param array $keys
     * @return static
     */
    public function exclude(array $keys)
    {
        $this->excluded_keys = $keys;

        return $this;
    }

    /**
     * get all only included fields
     *
     * @param array $keys
     * @return array 
     */
    public function only(array $keys)
    {
        $data = $this->all();

        return array_intersect_key($data, array_flip($keys));
    }

    /**
     * set all included fields
     * @param array $except keys
     * @return static
     */
    public function pick(array $keys)
    {
        $this->picked_keys = $keys;

        return $this;
    }

    /**
     * Get public properties with values
     * 
     * @return array
     */
    protected function get_public_vars(): array
    {
        $vars = [];
        $reflection = new \ReflectionClass($this);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $vars[$name] = $this->{$name};
        }

        return $vars;
    }

    /**
     * Get casts
     * 
     * @return array
     */
    protected function get_casts()
    {
        return $this->casts;
    }

    /**
     * Cast all attributes
     * 
     * @return $this
     */
    protected function cast_attributes()
    {
        if ($this->prepared_for_display) {
            return $this;
        }

        foreach ($this->get_casts() as $key => $cast) {
            $field = explode('.', $key);
            $attribute = $field[0];

            if (property_exists($this, $attribute)) {
                array_shift($field);
                $this->{$attribute} = $this->traverse_and_cast_attribute(
                    $this->{$attribute},
                    $field,
                    $cast
                );
            }
        }

        // Mark as prepared for display
        $this->prepared_for_display = true;

        return $this;
    }

    /**
     * Traverse and cast attribute
     * 
     * @param mixed $current_field_value
     * @param array $key_segments
     * @param CastAttribute|callable $cast
     * 
     * @return mixed
     */
    protected function traverse_and_cast_attribute($current_field_value, array $key_segments, $cast)
    {
        if (empty($key_segments)) {
            if (is_subclass_of($cast, CastAttribute::class)) {
                $attribute_class = new $cast();
                return $attribute_class->get($current_field_value, $current_field_value);
            } elseif (is_callable($cast)) {
                return $cast();
            }

            throw new Exception('Cast must be an instance of ' . CastAttribute::class . ' or a callable');
        }

        $segment = array_shift($key_segments);

        if ($segment === '*') {
            if (!is_array($current_field_value)) {
                return $current_field_value;
            }

            foreach ($current_field_value as $key => $value) {
                $current_field_value[$key] = $this->traverse_and_cast_attribute(
                    $value,
                    $key_segments,
                    $cast
                );
            }
        } elseif (is_array($current_field_value) && array_key_exists($segment, $current_field_value)) {
            $current_field_value[$segment] = $this->traverse_and_cast_attribute(
                $current_field_value[$segment],
                $key_segments,
                $cast
            );
        } elseif (is_object($current_field_value) && property_exists($current_field_value, $segment)) {
            $current_field_value->{$segment} = $this->traverse_and_cast_attribute(
                $current_field_value->{$segment},
                $key_segments,
                $cast
            );
        }

        return $current_field_value;
    }

    public function get_values()
    {
        return $this->cast_attributes();
    }
}
