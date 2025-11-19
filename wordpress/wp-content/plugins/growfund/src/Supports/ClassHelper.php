<?php

namespace Growfund\Supports;

defined( 'ABSPATH' ) || exit;

/**
 * Class Helper for managing CSS classes
 * Provides consistent class management across components
 */
class ClassHelper
{
    /**
     * Combine base classes with custom classes and return final class string
     *
     * @param array $base_classes Base classes to always include
     * @param string $custom_classes Custom classes to merge
     * @return string Final class string
     */
    public static function buildClassString(array $base_classes, string $custom_classes = ''): string
    {
        // Combine base classes with custom classes
        $all_classes = array_merge($base_classes, explode(' ', trim($custom_classes)));

        // Remove empty classes and create final class string
        return implode(' ', array_filter($all_classes));
    }

    /**
     * Build class string from variant configuration
     *
     * @param array $variant_configs All variant configurations
     * @param string $variant Selected variant
     * @param string $custom_classes Custom classes to merge
     * @return string Final class string
     */
    public static function buildClassStringFromVariant(array $variant_configs, string $variant, string $custom_classes = ''): string
    {
        // Get variant configuration
        $variant_config = $variant_configs[$variant] ?? $variant_configs['default'] ?? [];

        // Get variant classes
        $variant_classes = $variant_config['classes'] ?? [];

        return self::buildClassString($variant_classes, $custom_classes);
    }

    /**
     * Build HTML attributes string from array
     *
     * @param array $attributes Attributes array
     * @param array $dangerous_attributes List of dangerous attributes to skip
     * @return string HTML attributes string
     */
    public static function buildAttributesString(array $attributes, array $dangerous_attributes = []): string
    {
        $attribute_string = '';

        foreach ($attributes as $key => $value) {
            // Validate attribute keys to prevent injection
            $key = trim($key);
            if (empty($key) || !preg_match('/^[a-zA-Z][a-zA-Z0-9\-_]*$/', $key)) {
                continue; // Skip invalid attribute names
            }

            // Skip potentially dangerous attributes
            if (in_array(strtolower($key), $dangerous_attributes, true)) {
                continue;
            }

            $attribute_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        return $attribute_string;
    }

    /**
     * Get default dangerous attributes list
     *
     * @return array List of dangerous HTML attributes
     */
    public static function getDefaultDangerousAttributes(): array
    {
        return [
            'onload',
            'onerror',
            'onclick',
            'onmouseover',
            'onfocus',
            'onblur',
            'onchange',
            'onsubmit',
            'onreset',
            'onselect',
            'onunload',
            'onabort',
            'onbeforeunload',
            'onerror',
            'onhashchange',
            'onmessage',
            'onoffline',
            'ononline',
            'onpagehide',
            'onpageshow',
            'onpopstate',
            'onstorage',
            'oncontextmenu',
            'oncopy',
            'oncut',
            'onpaste',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onmousedown',
            'onmousemove',
            'onmouseout',
            'onmouseup',
            'onwheel',
            'onbeforecopy',
            'onbeforecut',
            'onbeforepaste',
            'onselectstart'
        ];
    }
}
