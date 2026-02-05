<?php

namespace Growfund\DTO;

defined( 'ABSPATH' ) || exit;

use Growfund\Traits\Castable;
use Growfund\Sanitizer;
use JsonSerializable;

/**
 * Base Data Transfer Object
 *
 * @since 1.0.0
 */
class DTO implements JsonSerializable
{
    use Castable;

    /**
     * Fields that are considered not part of "meta" data.
     *
     * @var array
     */
    protected static $base_fields = ['id', 'title', 'description', 'slug'];


    


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
}
