<?php

namespace Growfund\Traits;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Contracts\CastAttribute;
use Growfund\DTO\DTO;
use Growfund\View;

/**
 * @since 1.0.3
 */
trait Castable
{
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
            } elseif (
                is_a($cast, DTO::class, true) || is_subclass_of($cast, DTO::class) || is_a($cast, View::class, true) || is_subclass_of($cast, View::class)
            ) {
                if (!is_subclass_of($current_field_value, DTO::class) && !is_subclass_of($current_field_value, View::class)) {
                    return $current_field_value;
                }

                return $current_field_value->get_values();
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
