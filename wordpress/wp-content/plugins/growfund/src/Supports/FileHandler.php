<?php

namespace Growfund\Supports;

use Exception;

defined( 'ABSPATH' ) || exit;

class FileHandler
{
    protected $target_dir;
    protected $upload_dir;
    protected $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/zip'];

    /**
     * FileHandler constructor
     * @param string $upload_dir
     */
    public function __construct($upload_dir = '')
    {
        $this->upload_dir = !empty($upload_dir) ? ltrim(rtrim($upload_dir, '/'), '/') . '/' : '';

        $this->target_dir = WP_CONTENT_DIR . '/uploads/growfund-uploads/' . $this->upload_dir;

        if (!file_exists($this->target_dir)) {
            wp_mkdir_p($this->target_dir);
        }
    }

    /**
     * Upload file
     * 
     * @return array
     */
    public function upload($file, $filename = null, $old_file = null)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'error' => __('Upload failed', 'growfund')
            ];
        }

        // Validate type
        if (!in_array($file['type'], $this->allowed_types, true)) {
            return [
                'success' => false,
                'error' => __('Invalid file type', 'growfund')
            ];
        }

        // Generate unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if ($filename) {
            $filename = $filename . '.' . $ext;
		} else {
			$filename = growfund_uuid() . '.' . $ext;
        }

        $target = $this->get_path($filename);

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            return [
                'success' => false,
                'error' => __('Failed to move file', 'growfund')
            ];
        }

        if (!empty($old_file) && $old_file !== $filename) {
            $this->delete($old_file);
        }

        return [
            'success' => true,
            'file_name' => $filename,
            'file_path' => $this->upload_dir . $filename,
            'target_path' => $target
        ];
    }

    /**
     * Download file securely
     */
    public function download(string $file_path, ?string $download_name = null)
    {
        $full_path = $this->get_path($file_path);

        if (!file_exists($full_path)) {
            wp_die('File not found');
        }

        
        $download_name = !empty($download_name) 
            ? pathinfo($download_name, PATHINFO_FILENAME) 
            : pathinfo($full_path, PATHINFO_FILENAME);

        $ext = pathinfo($full_path, PATHINFO_EXTENSION);
        $download_name .= '.' . $ext;

        $mime = mime_content_type($full_path);

        if (ob_get_length()) {
			ob_end_clean();
		}

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $download_name . '"');
        header('Content-Length: ' . filesize($full_path));
        header('Pragma: public');

        readfile($full_path); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- read binary bites from file
        exit;
    }

    /**
     * Get full file path from filename
     */
    public function get_path(string $filename)
    {
        if (strpos($filename, $this->target_dir) === 0) {
            return $filename;
        }

        return rtrim($this->target_dir, '/') . '/' . $filename;
    }

    public function get_name(string $filename)
    {
        $full_path = $this->get_path($filename);

        return basename($full_path);
    }

    /**
     * Delete file
     */
    public function delete(string $filename)
    {
        $path = $this->get_path($filename);

        if (file_exists($path)) {
            wp_delete_file($path);
            return true;
        }

        return false;
    }

    /**
     * Reconstructs the $_FILES array into a clean, nested structure.
     * 
     * PHP's default $_FILES structure splits properties (name, tmp_name, etc.) at the 
     * top level for multi-dimensional inputs. This method regroup those properties 
     * back into individual file arrays while preserving the original input nesting.
     *
     * @param array $files The raw $_FILES array to be processed.
     * @return array A unified array where each leaf node contains all file details.
     */
    public static function format_files_form_request(array $files)
    {
		$result = [];

        foreach ($files as $inputName => $details) {
            foreach ($details as $property => $value) {
                static::format_files_recursively($value, $inputName, $property, $result);
            }
        }

        return $result;
    }

    /**
     * Recursively traverses nested file input data to map properties to their leaf nodes.
     *
     * @param mixed $data      The current level of data being processed (string or array).
     * @param string|int $key  The current index or name in the nesting hierarchy.
     * @param string $property The file property being mapped (e.g., 'name', 'tmp_name').
     * @param array &$result   The reference to the array where the structure is being built.
     * @return void
     */
    protected static function format_files_recursively($data, $key, $property, &$result)
    {   
        if (!is_array($data)) {
            $result[$key][$property] = $data;
            return;
        }

        foreach ($data as $subKey => $subValue) {
            static::format_files_recursively($subValue, $subKey, $property, $result[$key]);
        }
    }

    /**
     * Check if file is valid
     * 
     * @param array $file The file to check
     * @return bool
     */
    public static function is_valid_file($file) 
    {
        return !empty($file) 
        && is_array($file) 
        && isset($file['name'], $file['tmp_name'], $file['type'], $file['size'], $file['error']);
    }
}
