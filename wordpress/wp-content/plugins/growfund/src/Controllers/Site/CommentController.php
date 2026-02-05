<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Http\Response;
use Growfund\Validation\Validator;
use Growfund\Sanitizer;
use Growfund\DTO\Comment\CommentFilterDTO;
use Growfund\DTO\Comment\CreateCommentDTO;
use Growfund\DTO\JsonResponseDTO;
use Growfund\Services\CommentService;
use Growfund\Views\Components\Comments\CommentCollection;
use Growfund\Views\Components\Comments\Comment;
use Growfund\Views\Components\Comments\CommentReply;

class CommentController
{
    /**
     * @var CommentService
     */
    protected $service;

    /**
     * Initialize class with CommentService
     */
    public function __construct()
    {
        $this->service = new CommentService();
    }

    /**
     * Create a new comment
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\SiteResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request, CreateCommentDTO::validation_rules());

        if ($validator->is_failed()) {
            $errors = $validator->get_errors();
            return growfund_site_response()->json_error($errors, Response::UNPROCESSABLE_ENTITY);
        }

        $sanitized_data = Sanitizer::make($request, CreateCommentDTO::sanitization_rules())->get_sanitized_data();

        $dto = CreateCommentDTO::from_array($sanitized_data);

        $comment_id = $this->service->store($dto);

        $response_dto = new JsonResponseDTO([
            'html' => '',
            'data' => $comment_id,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    public function get_comment_by_id(Request $request) {
        $comment_id = $request->get_int('comment_id');

        $comment = $this->service->get_by_id($comment_id);
        
        $comment_view = $comment->comment_parent ? new CommentReply() : new Comment();
        $comment_view->comment = $comment;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($comment_view),
            'data' => $comment,
        ]);
        
        return growfund_site_response()->json($response_dto);
    }


    public function get_comments(Request $request) {
        $filter_dto = new CommentFilterDTO();
        $filter_dto->post_id = $request->get_int('post_id');
        $filter_dto->parent = $request->get_int('parent_id', 0);
        $filter_dto->page = $request->get_int('page', 1);
        $filter_dto->comment_type = $request->get_string('comment_type');

        $paginated = $this->service->paginated($filter_dto);
        
        $comment_collection = new CommentCollection();
        $comment_collection->comments = $paginated->results;
        $comment_collection->is_reply = $filter_dto->parent > 0;

        $response_dto = new JsonResponseDTO([
            'html' => growfund_get_safe_html($comment_collection),
            'data' => $paginated,
        ]);

        return growfund_site_response()->json($response_dto);
    }
}
