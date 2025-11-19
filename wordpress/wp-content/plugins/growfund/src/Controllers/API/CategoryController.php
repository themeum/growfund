<?php

namespace Growfund\Controllers\API;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Policies\CategoryPolicy;
use Growfund\Sanitizer;
use Growfund\Services\CampaignCategoryService;
use Growfund\Supports\Arr;
use Growfund\Validation\Validator;

/**
 * Class CategoryController
 * @since 1.0.0
 */
class CategoryController
{
    /**
     * @var CampaignCategoryService
     */
    private $service;

    /**
     * @var CategoryPolicy
     */
    private $policy;

    /**
     * CategoryController constructor.
     *
     * @since 1.0.0
     *
     * @param CampaignCategoryService $service
     */
    public function __construct(CampaignCategoryService $service, CategoryPolicy $policy)
    {
        $this->service = $service;
        $this->policy = $policy;
    }

    /**
     * Retrieve all campaign categories
     *
     * @since 1.0.0
     *
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
     * Retrieve all top level campaign categories
     *
     * @since 1.0.0
     *
     * @return Response
     */
    public function top_level()
    {
        return growfund_response()->json([
            'data' => $this->service->get_top_level_categories(),
            'message' => '',
        ], Response::OK);
    }

    /**
     * Retrieve all sub categories by parent id
     *
     * @since 1.0.0
     *
     * @param Request $request
     * @return Response
     */
    public function sub_categories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => 'required|integer'
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $parent_id = $request->get_int('parent_id');

        return growfund_response()->json([
            'data' => $this->service->get_sub_categories($parent_id),
            'message' => '',
        ], Response::OK);
    }

    /**
     * Create new campaign category
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $this->policy->authorize_create(growfund_user()->get_id());

        $validator = Validator::make($request->all(), [
            'name'               => 'required|string|max:150',
            'slug'               => 'string',
            'description'        => 'string',
            'parent_id'          => 'string',
            'image' => 'integer|is_valid_image_id'
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($request->all(), [
            'name'               => Sanitizer::TEXT,
            'slug'               => Sanitizer::TITLE,
            'description'        => Sanitizer::TEXTAREA,
            'parent_id'          => Sanitizer::INT,
            'image'              => Sanitizer::INT,
        ])->get_sanitized_data();

        $result = $this->service->create($sanitized_data);

        $response = [
            'data' => ['id' => (string) $result['term_id']],
            'message' => __('Category created successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::CREATED);
    }

    /**
     * Update campaign category
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $this->policy->authorize_update(growfund_user()->get_id());

        $validator = Validator::make($request->all(), [
            'id'                 => 'required|integer',
            'name'               => 'required|string|max:150',
            'slug'               => 'string',
            'description'        => 'string',
            'parent_id'          => 'string',
            'image' => 'integer|is_valid_image_id',
        ]);

        if ($validator->is_failed()) {
            throw ValidationException::with_errors($validator->get_errors()); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $id = $request->get_int('id');

        $sanitized_data = Sanitizer::make($request->all(), [
            'name' => Sanitizer::TEXT,
            'slug' => Sanitizer::TITLE,
            'description' => Sanitizer::TEXTAREA,
            'parent_id' => Sanitizer::INT,
            'image' => Sanitizer::INT,
        ])->get_sanitized_data();

        $result = $this->service->update($id, $sanitized_data);

        $response = [
            'data' => $result,
            'message' => __('Category updated successfully.', 'growfund'),
        ];

        return growfund_response()->json($response, Response::OK);
    }

    /**
     * Delete campaign category and subcategory
     * @param Request $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $this->policy->authorize_delete(growfund_user()->get_id());

        $id = $request->get_int('id');

        $result = $this->service->delete($id);

        $response = [
            'data' => $result,
            'message' => $result ? __('Category deleted successfully.', 'growfund') : __('Failed to delete category.', 'growfund'),
        ];

        $code = $result ? Response::NO_CONTENT : Response::NOT_FOUND;

        return growfund_response()->json($response, $code);
    }

    /**
     * Empty trashed campaign categories
     *
     * @return Response
     */
    public function empty_trash()
    {
        $this->policy->authorize_empty_trash(growfund_user()->get_id());

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

    public function bulk_actions(Request $request)
    {
        $this->policy->authorize_bulk_actions(growfund_user()->get_id());

        $validator = Validator::make($request->all(), [
            'action' => 'required|in:delete',
            'ids' => 'required|array',
            'ids.*' => 'integer',
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
                ? __('Bulk action successfully applied for all the selected categories.', 'growfund')
                : sprintf(
                    /* translators: %s: category ids */
                    __('Bulk action successfully applied for all the selected categories except the categories with id: %s.', 'growfund'),
                    implode(', ', $failed)
                );

            return growfund_response()->json([
                'data' => $result,
                'message' => $message,
            ], Response::MULTI_STATUS);
        }
    }
}
