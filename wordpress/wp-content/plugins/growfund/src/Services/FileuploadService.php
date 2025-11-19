<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Uploader;
use Growfund\Supports\MediaAttachment;
use Exception;

/**
 * File upload service class
 * 
 * @since 1.0.0
 */
class FileuploadService implements Uploader
{

    /**
     * Create a new fileupload service instance.
     * 
     * @return self
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Upload one or more files and attach them to a WordPress post.
     *
     * @param array $files          Array of file data.
     * @param int   $parent_post_id (Optional) Post ID to attach files to.
     *
     * @return array Array of formatted image attachment data.
     * @throws Exception If the file structure is invalid or upload fails.
     */
    public function upload(array $files, int $parent_post_id = 0)
    {
        if (!isset($files['name'], $files['tmp_name'], $files['type'], $files['size'], $files['error'])) {
            throw new Exception('Invalid file upload array structure.');
        }

        $formatted_files = $this->format_files($files);
        $attachments = [];

        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        $author_id = get_current_user_id();

        foreach ($formatted_files as $file) {
            $upload = wp_handle_upload($file, ['test_form' => false]);

            if (isset($upload['error'])) {
                /* translators: %s: file upload error */
                throw new Exception(sprintf(esc_html__('File upload error: %s', 'growfund'), esc_html($upload['error'])));
            }

            if (isset($upload['file'])) {
                $filetype = wp_check_filetype($upload['file']);
                $file_url  = $upload['url'];
                $attachment = [
                    'post_mime_type' => $filetype['type'],
                    'post_title' => sanitize_file_name($file['name']),
                    'post_content' => '',
                    'post_status' => 'inherit',
                    'post_author'    => $author_id,
                    'guid'           => $file_url
                ];

                $attachment_id = wp_insert_attachment($attachment, $upload['file'], $parent_post_id);

                if (!function_exists('wp_generate_attachment_metadata')) {
                    require_once ABSPATH . 'wp-admin/includes/image.php';
                    require_once ABSPATH . 'wp-admin/includes/media.php';
                }

                $attachment_meta_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);

                wp_update_attachment_metadata($attachment_id, $attachment_meta_data);

                $attachments[] = MediaAttachment::make($attachment_id);
            }
        }

        return $attachments;
    }

    /**
     * Formats the uploaded files array into a more accessible structure.
     * 
     * @param array $files The array of files from the $_FILES superglobal.
     * 
     * @return array An array of formatted files, where each file is an associative array
     *               containing its properties.
     */

    private function format_files(array $files)
    {
        $formatted_files = [];
        $file_count = count($files['name']);
        $file_keys = array_keys($files);

        for ($index = 0; $index < $file_count; $index++) {
            foreach ($file_keys as $key) {
                $formatted_files[$index][$key] = $files[$key][$index];
            }
        }

        return $formatted_files;
    }
}
