<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Services\FileuploadService;
use Growfund\Validation\Validator;

/**
 * Media controller class
 * @since 1.0.0
 */
class MediaController
{
    /**
     * Upload media files
     * @param Request $request{
     *     @type array media
     * }
     * 
     * @return Response
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'media'              => 'required',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $media_files = $request->get_array('media');

        $attachments = FileuploadService::create()->upload($media_files);

        $response = [
            'data' => [
                'media' => $attachments
            ],
            'message' => __('Media files are uploaded successfully', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }
}
