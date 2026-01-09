<?php

defined( 'ABSPATH' ) || exit;

/**
 * Form Builder Component
 * Lightweight wrapper around existing components to reduce boilerplate
 * 
 * @param array $fields - Array of field definitions
 * @param array $form_attributes - Form tag attributes
 * @param string $submit_button_text - Submit button text
 * @param array $submit_button_attributes - Submit button attributes
 * @param array $errors - Array of field errors (field_name => error_message)
 */

$fields = $fields ?? [];
$form_attributes = $form_attributes ?? [];
$submit_button_text = $submit_button_text ?? null;
$submit_button_attributes = $submit_button_attributes ?? [];
$errors = $errors ?? []; // phpcs:ignore -- intentionally used

$default_form_attributes = [
    'method' => 'POST',
    'action' => ''
];
$form_attributes = array_merge($default_form_attributes, $form_attributes);

$form_attr_string = '';
foreach ($form_attributes as $key => $value) {
    $form_attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
}

/**
 * Helper function to render a field based on its configuration
 */
function render_form_field($field_config, $errors = [])
{
    $type = $field_config['type'] ?? 'input';
    $component_data = $field_config['data'] ?? [];

    // If no 'data' key exists, use the field config directly as component data
    if (empty($component_data)) {
        $component_data = $field_config;
    }

    // Ensure name is passed to component_data for ID generation
    if (!empty($field_config['name']) && empty($component_data['name'])) {
        $component_data['name'] = $field_config['name'];
    }

    // Generate ID for label association if not provided
    if (empty($component_data['id']) && !empty($component_data['name'])) {
        $component_data['id'] = 'input-' . sanitize_title($component_data['name']);
    }

    // Special handling for payment-method-selector - use direct parameters instead of data
    if ($type === 'payment-method-selector') {
        $component_data = [
            'methods' => $field_config['methods'] ?? [],
            'selected' => $field_config['selected'] ?? '',
            'name' => $field_config['name'] ?? 'payment_method',
            'default_icon' => $field_config['default_icon'] ?? 'cash-on-delivery'
        ];
    }

    $is_hidden_field = ($type === 'input' && isset($component_data['type']) && $component_data['type'] === 'hidden');

    if ($is_hidden_field) {
        $component_template = 'site.components.input';

        try {
            return growfund_renderer()->render($component_template, $component_data);
        } catch (Exception $e) {
            return '<div class="growfund-form-error">Error rendering field: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    // Check if this is an HTML field - if so, render content directly
    if ($type === 'html') {
        $content = $field_config['content'] ?? '';
        return $content;
    }

    // Special handling for div type (wrapper with nested fields)
    if ($type === 'div') {
        $wrapper_class = $field_config['wrapper_class'] ?? '';
        $wrapper_attributes = $field_config['wrapper_attributes'] ?? [];
        $fields = $field_config['fields'] ?? [];

        $attr_string = '';
        foreach ($wrapper_attributes as $key => $value) {
            $attr_string .= ' ' . esc_attr($key) . '="' . esc_attr($value) . '"';
        }

        $output = '<div class="' . esc_attr($wrapper_class) . '"' . $attr_string . '>';

        foreach ($fields as $field) {
            if (isset($field['type']) && $field['type'] === 'group') {
                $output .= render_field_group($field, $errors);
            } else {
                $output .= render_form_field($field, $errors);
            }
        }

        $output .= '</div>';
        return $output;
    }

    if ($type === 'row') {
        $fields = $field_config['fields'] ?? [];
        $wrapper_class = $field_config['wrapper_class'] ?? 'growfund-form-row-half';

        $output = '<div class="' . esc_attr($wrapper_class) . '">';

        foreach ($fields as $field) {
            $output .= render_form_field($field, $errors);
        }

        $output .= '</div>';
        return $output;
    }

    $wrapper_class = $field_config['wrapper_class'] ?? '';
    $has_custom_wrapper = !empty($wrapper_class);

    if (!$has_custom_wrapper) {
        $wrapper_class = 'growfund-form-field';
    }

    $field_name = $component_data['name'] ?? '';
    $has_errors = false;

    if (!empty($field_name) && isset($errors[$field_name])) {
        $has_errors = true;
    } else {
        $dot_notation_name = str_replace(['[', ']'], ['.', ''], $field_name);
        if (!empty($dot_notation_name) && isset($errors[$dot_notation_name])) {
            $has_errors = true;
        }
    }

    if ($has_errors) {
        $wrapper_class = $wrapper_class ? $wrapper_class . ' growfund-form-field--error' : 'growfund-form-field growfund-form-field--error';
    }

    if ($type === 'checkbox') {
        $wrapper_class = $wrapper_class ? $wrapper_class . ' growfund-checkbox-field' : 'growfund-checkbox-field';
    } elseif ($type === 'radio') {
        $wrapper_class = $wrapper_class ? $wrapper_class . ' growfund-radio-field' : 'growfund-radio-field';
    } elseif ($type === 'input') {
        $input_type = $component_data['type'] ?? 'text';
        if ($input_type === 'radio') {
            $wrapper_class = $wrapper_class ? $wrapper_class . ' growfund-radio-field' : 'growfund-radio-field';
        } elseif (in_array($input_type, ['text', 'email', 'tel', 'password'], true)) {
            $wrapper_class = $wrapper_class ? $wrapper_class . ' growfund-input-field' : 'growfund-input-field';
        }
    }

    $wrapper_start = '<div class="' . esc_attr($wrapper_class) . '">';
    $wrapper_end = '</div>';

    $error_message = '';
    if ($has_errors) {
        $error_key = $field_name;
        if (!isset($errors[$field_name])) {
            $error_key = str_replace(['[', ']'], ['.', ''], $field_name);
        }
        $error_message = '<div class="growfund-field-error-msg" style="color: #D40000; font-size: 12px;">' . esc_html(is_array($errors[$error_key]) ? implode(', ', $errors[$error_key]) : $errors[$error_key]) . '</div>';
    }

    $label = '';
    if (!empty($field_config['label'])) {
        $for_attr = !empty($component_data['id']) ? ' for="' . esc_attr($component_data['id']) . '"' : '';
        $label_class = $field_config['label_class'] ?? 'growfund-form-label';
        $label = '<label class="' . esc_attr($label_class) . '"' . $for_attr . '>' . $field_config['label'] . '</label>';
    }

    $description = '';
    if (!empty($field_config['description'])) {
        $description = '<p class="growfund-form-description">' . esc_html($field_config['description']) . '</p>';
    }

    $component_map = [
        'input' => 'site.components.input',
        'dropdown' => 'site.components.dropdown',
        'textarea' => 'site.components.textarea',
        'payment-method-selector' => 'site.components.payment-method-selector',
        'checkbox' => 'site.components.input',
        'radio' => 'site.components.input',
        'button' => 'site.components.button'
    ];

    if ($type === 'html') {
        $field_html = $field_config['content'] ?? '';
        return $field_html;
    } else {
        $component_template = $component_map[$type] ?? 'site.components.input';

        $custom_attributes = [];

        foreach ($field_config as $key => $value) {
            if (!in_array($key, ['type', 'data', 'label', 'description', 'wrapper_class', 'label_class'], true)) {
                $custom_attributes[$key] = $value;
            }
        }

        if ($has_errors) {
            $custom_attributes['data-has-errors'] = 'true';
        }

        if (isset($field_config['data']) && is_array($field_config['data'])) {
            foreach ($field_config['data'] as $key => $value) {
                $excluded_attributes = ['options', 'name', 'id', 'placeholder', 'required', 'variant', 'class', 'type'];
                if ($type === 'textarea') {
                    $excluded_attributes = array_merge($excluded_attributes, ['rows', 'cols', 'maxlength', 'minlength', 'readonly', 'disabled']);
                }

                if (strpos($key, 'data-') === 0 || !in_array($key, $excluded_attributes, true)) {
                    $custom_attributes[$key] = $value;
                }
            }
        }

        $final_component_data = array_merge($component_data, ['attributes' => $custom_attributes]);

        if ($type === 'textarea') {
            $textarea_attributes = ['rows', 'cols', 'maxlength', 'minlength', 'readonly', 'disabled'];
            foreach ($textarea_attributes as $attr) {
                if (isset($component_data[$attr])) {
                    $final_component_data[$attr] = $component_data[$attr];
                }
            }
        }

        try {
            $field_html = growfund_renderer()->get_html($component_template, $final_component_data);
        } catch (Exception $e) {
            $field_html = '<div class="growfund-form-error">Error rendering field: ' . esc_html($e->getMessage()) . '</div>';
        }
    }

    if ($type === 'checkbox' || $type === 'radio' || ($type === 'input' && ($component_data['type'] ?? 'text') === 'radio')) {
        return $wrapper_start . $error_message . $label . $field_html . $description . $wrapper_end;
    }

    return $wrapper_start . $label . $field_html . $description . $error_message . $wrapper_end;
}

/**
 * Helper function to render a field group (section)
 */
function render_field_group($group_config, $errors = [])
{
    $title = $group_config['title'] ?? '';
    $fields = $group_config['fields'] ?? [];
    $wrapper_class = $group_config['wrapper_class'] ?? 'growfund-form-section';

    $output = '<div class="' . esc_attr($wrapper_class) . '">';

    if ($title) {
        $title_class = $group_config['title_class'] ?? 'growfund-form-title';
        $output .= '<h2 class="' . esc_attr($title_class) . '">' . esc_html($title) . '</h2>';
    }

    foreach ($fields as $field) {
        if (isset($field['type']) && $field['type'] === 'group') {
            $output .= render_field_group($field, $errors);
        } else {
            $output .= render_form_field($field, $errors);
        }
    }

    $output .= '</div>';

    return $output;
}

?>

<form<?php echo $form_attr_string; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?> class="growfund-form-builder">
    <?php foreach ($fields as $field_or_group) : ?>
        <?php if (isset($field_or_group['type']) && $field_or_group['type'] === 'group') : ?>
            <?php echo render_field_group($field_or_group, $errors); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
        <?php elseif (isset($field_or_group['type']) && $field_or_group['type'] === 'div') : ?>
            <?php echo render_form_field($field_or_group, $errors); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
        <?php elseif (isset($field_or_group['type']) && $field_or_group['type'] === 'row') : ?>
            <?php echo render_form_field($field_or_group, $errors); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
        <?php else : ?>
            <?php echo render_form_field($field_or_group, $errors); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped ?>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php growfund_nonce_field(); ?>

    <?php if ($submit_button_text) : ?>
        <?php
        $default_submit_attributes = [
            'text' => $submit_button_text,
            'type' => 'submit'
        ];

        $submit_attributes = array_merge($default_submit_attributes, $submit_button_attributes);

        growfund_renderer()->render('site.components.button', $submit_attributes);
        ?>
    <?php endif; ?>
    </form>