<?php

namespace Growfund\Validation\Rules;

use Growfund\Supports\FileHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Validates that a value is present and not null.
 *
 * @since 1.0.0
 */
class FileRule extends BaseRule
{
    protected $allowed_mimes = [
        'image/*',
        'application/pdf',
        'application/zip'
    ];

    protected $default_max_size = 5 * 1024 * 1024; // 5 MB * 1024 KB * 1024 Bytes = 5242880 bytes

    protected $error = null;

    /**
     * Determine if the value is present.
     *
     * @return bool
     */
    public function validate_rule()
    {
        if (!FileHandler::is_valid_file($this->value)) {
            $this->error = __('Invalid file.', 'growfund');
            return false;
        }

        $check_file = wp_check_filetype_and_ext($this->value['tmp_name'], $this->value['name']);

        if (!$check_file['ext'] || !$check_file['type']) {
            $this->error = __('Invalid file.', 'growfund');
            return false;
        }

        if (empty($this->value['size'])) {
            $this->error = __('The file is required.', 'growfund');
            return false;
        }

        $size = $this->rule_value ?? $this->default_max_size;

        if ($this->value['size'] > (int) $size) {
            $this->error = sprintf('Max file size is %sMB', ((int) $size / 1024 / 1024));

            return false;
        }

        $mime = mime_content_type($this->value['tmp_name']);

        if ($mime && !$this->isMimeAllowed($mime)) {
            $this->error = 'Invalid file type';
            return false;
        }

        return true;
    }

    protected function isMimeAllowed(string $mime)
    {
        foreach ($this->allowed_mimes as $allowed) {
            if (strpos($allowed, '/*') !== false) {
                $base = explode('/', $allowed)[0];
                if (strpos($mime, $base . '/') === 0) {
                    return true;
                }
            }

            if ($mime === $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the error message for a missing required field.
     *
     * @return string
     */
    public function get_error_message()
    {
        /* translators: %s: field name */
        return $this->error ?? sprintf(__('The %s field is required.', 'growfund'), str_replace(['_', '.'], ' ', $this->key));
    }
}
