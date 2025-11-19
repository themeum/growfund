<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\TagsPolicy;
use Growfund\Sanitizer;
use Growfund\Services\CampaignTagService;
use Growfund\Supports\Arr;
use Growfund\Validation\Validator;

/**
 * Class TagController
 * @since 1.0.0
 */
class TagController
{
    /**
     * @var CampaignTagService
     * @access private
     */
    private $service;

    /**
     * @var TagsPolicy
     * @access private
     */
    private $policy;

    /**
     * TagController constructor.
     */
    public function __construct(CampaignTagService $service, TagsPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Retrieve all campaign tags
     * @return Response
     */
    public function list()
    {
        return growfund_response()->json([
            'data' => $this->service->get_all(),
            'message' => '',
        ], Response::OK);
    }

    /**
     * Create new campaign tag
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $this->policy->authorize_create();

        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:150',
            'slug'               => 'string',
            'description'        => 'string'
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($request->all(), [
            'name'               => Sanitizer::TEXT,
            'slug'               => Sanitizer::TITLE,
            'description'        => Sanitizer::TEXTAREA,
        ])->get_sanitized_data();

        $result = $this->service->create($sanitized_data);

        $response = [
            'data' => ['id' => (string) $result['term_id']],
            'message' => __('Tag created successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update campaign tag
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $this->policy->authorize_update();

        $validator = Validator::make($request->all(), [
            'id'                 => 'required|integer',
            'name'               => 'required|string|max:150',
            'slug'               => 'string',
            'description'        => 'string',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $id = $request->get_int('id');

        $sanitized_data = Sanitizer::make($request->all(), [
            'name' => Sanitizer::TEXT,
            'slug' => Sanitizer::TITLE,
            'description' => Sanitizer::TEXTAREA,
        ])->get_sanitized_data();

        $result = $this->service->update($id, $sanitized_data);

        $response = [
            'data' => $result,
            'message' => __('Tag updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Delete campaign tags
     *
     * @since 1.0.0
     *
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $this->policy->authorize_delete();

        $id = $request->get_int('id');

        $result = $this->service->delete($id);

        $response = [
            'data' => $result,
            'message' => $result ? __('Tag deleted successfully.', 'growfund') : __('Failed to delete tag.', 'growfund'),
        ];

        $code = $result ? Response::NO_CONTENT : Response::NOT_FOUND;

        return growfund_response()->json($response, $code);
    }

    /**
     * Empty trashed campaign tags
     *
     * @return Response
     */
    public function empty_trash()
    {
        $this->policy->authorize_empty_trash();

        $is_deleted = $this->service->empty_trash();

        if (!$is_deleted) {
            return growfund_response()->json([
                'data' => $is_deleted,
                'message' => __('Failed to empty trash', 'growfund'),
            ], Response::INTERNAL_SERVER_ERROR);
        }

        return growfund_response()->json([
            'data' => $is_deleted,
            'message' => __('Trash emptied successfully', 'growfund'),
        ]);
    }

    /**
     * Handle bulk actions
     *
     * @since 1.0.0
     *
     * @param \Growfund\Contracts\Request $request
     * @return Response
     * @throws \Growfund\Exceptions\ValidationException
     */
    public function bulk_actions(Request $request)
    {
        $this->policy->authorize_bulk_actions();

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'ids' => 'required|array',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $action = $request->get_string('action');
        $ids = $request->get_array('ids');

        if ($action === 'delete') {
            $result = $this->service->bulk_delete($ids);

            $failed = Arr::make($result['failed'])->pluck('id')->toArray();
            $message = empty($failed)
                ? __('Bulk action successfully applied for all the selected tags.', 'growfund')
                : sprintf(
                    /* translators: %s: tag ids */
                    __('Bulk action successfully applied for all the selected tags except the tags with id: %s.', 'growfund'),
                    implode(', ', $failed)
                );

            return growfund_response()->json([
                'data' => $result,
                'message' => $message,
            ], Response::MULTI_STATUS);
        }
    }
}
