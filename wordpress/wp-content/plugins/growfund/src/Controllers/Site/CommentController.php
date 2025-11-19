<?php

namespace Growfund\Controllers\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\Contracts\Request;
use Growfund\Core\AppSettings;
use Growfund\Exceptions\ValidationException;
use Growfund\Http\Response;
use Growfund\Services\Site\CommentService;
use Growfund\Validation\Validator;
use Growfund\Sanitizer;
use Exception;
use Growfund\DTO\Site\Comment\CreateCommentDTO;
use Growfund\DTO\Site\Comment\CommentDTO;
use Growfund\DTO\Site\AjaxResponseDTO;
use Growfund\DTO\Site\Comment\CommentCardItemDTO;
use Growfund\DTO\Site\Comment\CommentFormDTO;

class CommentController
{
    /**
     * @var CommentService
     */
    protected $comment_service;

    /**
     * Initialize class with CommentService
     */
    public function __construct()
    {
        $this->comment_service = new CommentService();
    }

    /**
     * Create a new comment
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, CreateCommentDTO::validation_rules());

        if ($validator->is_failed()) {
            $errors = $validator->get_errors();
            throw ValidationException::with_errors($errors); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- validation exception intentionally ignored
        }

        $sanitized_data = Sanitizer::make($data, CreateCommentDTO::sanitization_rules())->get_sanitized_data();

        $dto = CreateCommentDTO::from_array($sanitized_data);

        if (!growfund_settings(AppSettings::CAMPAIGNS)->allow_comments()) {
            return $this->send_disabled_comments_error();
        }

        if (!$this->comment_service->can_user_post_campaign_comments($dto->post_id, growfund_user()->get_id())) {
            return $this->send_access_denied_error();
        }

        $user_id = growfund_user()->get_id();

        $formatted_comment = $this->comment_service->create_comment($dto, $user_id);

        return growfund_site_response()->json($formatted_comment);
    }

    /**
     * Get reply form template
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\Response
     */
    public function get_reply_form_template(Request $request)
    {
        $data = $request->all();

        $template_dto = new CommentFormDTO($data);
        $template_dto->comment_type = (string) ($data['comment_type'] ?? 'comment');

        if (!growfund_settings(AppSettings::CAMPAIGNS)->allow_comments()) {
            return $this->send_disabled_comments_error();
        }

        if (!$this->comment_service->can_user_post_campaign_comments($template_dto->post_id, growfund_user()->get_id())) {
            return $this->send_access_denied_error();
        }

        $reply_form_dto = $this->comment_service->prepare_reply_form_template_data(
            $template_dto->post_id,
            $template_dto->comment_id,
            $template_dto->author_name,
            $template_dto->comment_type
        );

        $template_data = $reply_form_dto->to_array();

        $html = growfund_renderer()->get_html('site.components.comment-form', $template_data);

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Get comment HTML template
     * @param \Growfund\Http\Request $request
     * @return \Growfund\Http\Response
     */
    public function get_comment_template(Request $request)
    {
        $data = $request->all();

        $template_dto = new CommentFormDTO();
        $template_dto->comment_data_json = (string) ($data['comment_data'] ?? '');
        $template_dto->is_reply = filter_var($data['is_reply'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $template_dto->comment_type = (string) ($data['comment_type'] ?? 'comment');

        $comment_dto = $this->process_comment_template_request($data);

        // Check if user can see campaign comments
        if (!$this->comment_service->can_user_see_campaign_comments($comment_dto->post_id ?? 0)) {
            $is_own_comment = growfund_user()->is_logged_in() && (int) $comment_dto->user_id === growfund_user()->get_id();

            if (!$is_own_comment) {
                return $this->send_access_denied_error();
            }
        }

        $html = growfund_renderer()->get_html('site.components.comment', $this->comment_service->prepare_comment_template_data(
            $comment_dto,
            $template_dto->is_reply,
            $template_dto->comment_type
        ));

        $response_dto = new AjaxResponseDTO([
            'html' => $html,
        ]);

        return growfund_site_response()->json($response_dto);
    }

    /**
     * Process comment template request data
     *
     * @param array $data
     * @return \Growfund\DTO\Site\Comment\CommentCardItemDTO
     * @throws \Exception
     */
    protected function process_comment_template_request(array $data)
    {
        if (!isset($data['comment_data'])) {
            throw new Exception(esc_html__('Comment data is required.', 'growfund'));
        }

        $comment_data_json = stripslashes($data['comment_data']);

        if (!growfund_is_valid_json($comment_data_json)) {
            throw new Exception(esc_html__('Invalid comment data format.', 'growfund'));
        }

        $comment_data = json_decode($comment_data_json, true);

        if (!$comment_data) {
            throw new Exception(esc_html__('Failed to decode comment data.', 'growfund'));
        }

        return CommentCardItemDTO::from_array($comment_data);
    }

    /**
     * Send error response for disabled comments
     *
     * @return \Growfund\Http\Response
     */
    protected function send_disabled_comments_error()
    {
        $error_response = [
            'message' => __('Comments are currently disabled.', 'growfund'),
            'code' => Response::FORBIDDEN,
        ];
        return growfund_site_response()->json_error($error_response, $error_response['code']);
    }

    /**
     * Send error response for access denied
     *
     * @return \Growfund\Http\Response
     */
    protected function send_access_denied_error()
    {
        $error_response = [
            'message' => __('You do not have permission to post comments on this campaign.', 'growfund'),
            'code' => Response::FORBIDDEN,
        ];
        return growfund_site_response()->json_error($error_response, $error_response['code']);
    }
}
