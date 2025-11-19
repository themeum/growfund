<?php

namespace Growfund\Services\Site;

defined( 'ABSPATH' ) || exit;

use Growfund\DTO\Site\Comment\CommentDTO;
use Growfund\DTO\Site\Comment\CommentCardItemDTO;
use Growfund\DTO\Site\Comment\CreateCommentDTO;
use Growfund\DTO\Site\Comment\CommentFormDTO;
use Growfund\Supports\UserMeta;
use Growfund\Constants\Site\Comment as CommentConstants;
use Growfund\Constants\Site\CommentModeration;
use Growfund\Constants\Site\Visibility;
use Growfund\Core\AppSettings;
use Growfund\DTO\Site\Comment\CommentSettingsDTO;
use Growfund\PostTypes\Campaign;
use Exception;
use Growfund\Http\Response;
use WP_Comment;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;
use Growfund\Supports\MediaAttachment;

/**
 * Comment Service Class
 * 
 * Handles all comment-related business logic including creation, retrieval,
 * likes, replies, and data formatting.
 * 
 * @since 1.0.0
 */
class CommentService
{
    protected $campaign_settings;

    /**
     * Initialize class
     */
    public function __construct()
    {
        $this->campaign_settings = growfund_settings(AppSettings::CAMPAIGNS);
    }

    /**
     * Create a new comment using DTO
     * 
     * @param CreateCommentDTO $create_dto
     * @param int $user_id
     * @return CommentCardItemDTO
     * @throws Exception
     */
    public function create_comment(CreateCommentDTO $create_dto, int $user_id)
    {
        $this->validate_post_exists($create_dto->post_id);

        $user = growfund_user($user_id);
        $comment_data = [
            'comment_post_ID' => $create_dto->post_id,
            'comment_content' => wp_kses_post($create_dto->comment_content),
            'comment_parent' => $create_dto->parent_id,
            'comment_type' => $create_dto->comment_type,
        ];

        $comment_data['user_id'] = $user_id;
        $comment_data['comment_author'] = $user->get_display_name();
        $comment_data['comment_author_email'] = $user->get_email();
        $comment_data['comment_author_url'] = $user->get_meta('url');

        if ($this->campaign_settings->comment_moderation() === CommentModeration::NEED_APPROVAL) {
            $comment_data['comment_approved'] = CommentConstants::COMMENT_UNAPPROVED_STATUS;
        } else {
            $comment_data['comment_approved'] = CommentConstants::COMMENT_APPROVED_STATUS;
        }

        $comment_id = wp_insert_comment($comment_data);

        if (!$comment_id) {
            throw new Exception(esc_html__('Failed to create comment.', 'growfund'));
        }

        $comment = get_comment($comment_id);
        $user_data = $this->get_single_user_data($user_id);

        return $this->prepare_comment_dto($comment, [], $user_data);
    }

    /**
     * Get comments for a post with optimized queries
     * 
     * @param CommentDTO $query_dto
     * @return CommentDTO
     */
    public function get_comments(CommentDTO $query_dto)
    {
        // Only check if comments are enabled for the campaign
        // Don't check user visibility here - that's handled in the template
        if (!$this->campaign_settings->allow_comments()) {
            return $this->create_empty_comment_dto($query_dto->page, $query_dto->per_page);
        }

        $offset = ($query_dto->page - 1) * $query_dto->per_page;

        $comments = $this->fetch_comments($query_dto->post_id, $query_dto->per_page, $offset, $query_dto->comment_type);

        if (empty($comments)) {
            return $this->create_empty_comment_dto($query_dto->page, $query_dto->per_page);
        }

        $total = $this->get_comments_count($query_dto->post_id, $query_dto->comment_type);
        $formatted_comments = $this->format_comments_with_replies($comments, $query_dto->comment_type);

        $dto = new CommentDTO([
            'comments' => $formatted_comments,
            'page' => $query_dto->page,
            'per_page' => $query_dto->per_page,
            'total' => $total
        ]);

        $dto->current_page = $query_dto->page;
        $dto->total_posts = $total;
        $dto->total_pages = $query_dto->per_page > 0 ? (int) ceil($total / $query_dto->per_page) : 1;
        $dto->has_more = ($query_dto->page * $query_dto->per_page) < $total;

        return $dto;
    }

    /**
     * Delete a comment
     * 
     * @param int $comment_id
     * @param int $user_id
     * @return bool
     * @throws Exception
     */
    public function delete_comment(int $comment_id, int $user_id)
    {
        $comment = get_comment($comment_id);

        if (!$comment) {
            throw new Exception(esc_html__('Comment not found.', 'growfund'));
        }

        if (!$this->can_user_delete_comment($comment, $user_id)) {
            throw new Exception(esc_html__('You do not have permission to delete this comment.', 'growfund'), (int) Response::UNAUTHORIZED);
        }

        $deleted = wp_delete_comment($comment_id, true);

        if (!$deleted) {
            throw new Exception(esc_html__('Failed to delete comment.', 'growfund'));
        }

        return true;
    }



    /**
     * Check if user can delete a comment
     * 
     * @param WP_Comment $comment
     * @param int $user_id
     * @return bool
     */
    protected function can_user_delete_comment($comment, $user_id)
    {
        if (current_user_can('manage_options')) {
            return true;
        }

        $post = get_post($comment->comment_post_ID);

        if ($post && (int) $post->post_author === (int) $user_id) {
            return true;
        }

        if ((int) $comment->user_id === (int) $user_id) {
            return true;
        }

        return false;
    }

    /**
     * Get comments count for a post (optimized)
     * 
     * @param int $post_id
     * @param string $comment_type
     * @return int
     */
    public function get_comments_count(int $post_id, string $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE)
    {
        // Only check if comments are enabled for the campaign
        // Don't check user visibility here - that's handled in the template
        if (!$this->campaign_settings->allow_comments()) {
            return 0;
        }

        $approved_count = (int) get_comments([
            'post_id' => $post_id,
            'status' => CommentConstants::STATUS_APPROVED,
            'parent' => CommentConstants::DEFAULT_PARENT_ID,
            'comment_type' => $comment_type,
            'count' => true
        ]);

        if ($this->can_user_see_own_unapproved_comments($post_id)) {
            $current_user_id = growfund_user()->get_id();

            $user_unapproved_count = (int) get_comments([
                'post_id' => $post_id,
                'status' => 'hold',
                'parent' => CommentConstants::DEFAULT_PARENT_ID,
                'user_id' => $current_user_id,
                'comment_type' => $comment_type,
                'count' => true
            ]);

            return $approved_count + $user_unapproved_count;
        }

        return $approved_count;
    }

    /**
     * Prepare comment form data with user authentication state
     * 
     * @param CommentFormDTO $form_dto
     * @return CommentFormDTO|array
     */
    public function prepare_comment_form_data(CommentFormDTO $form_dto)
    {
        $allow_comment_form = $this->check_comment_form_enabled();

        if (!$allow_comment_form) {
            return [];
        }

        $user_info = $this->get_current_user_info();
        $form_texts = $this->get_form_texts($form_dto->is_reply ?? false, $form_dto->placeholder ?? '', $form_dto->button_text ?? '');

        $form_dto->allow_comments = $allow_comment_form;
        $form_dto->is_logged_in = $user_info['is_logged_in'];
        $form_dto->current_user_name = $user_info['name'];
        $form_dto->current_user_avatar = $user_info['avatar'];
        $form_dto->placeholder = $form_texts['placeholder'];
        $form_dto->button_text = $form_texts['button_text'];
        $form_dto->parent_id = CommentConstants::DEFAULT_PARENT_ID;

        $form_dto->comment_form_html = growfund_renderer()->get_html('site.components.comment-form', $form_dto->to_array());

        return $form_dto;
    }

    /**
     * Get comment settings for a specific post
     * 
     * @param int $post_id
     * @param string $comment_type
     * @return \Growfund\DTO\Site\Comment\CommentSettingsDTO
     */
    public function get_comment_settings(int $post_id, string $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE)
    {
        $dto = new CommentSettingsDTO();
        $dto->post_id = $post_id;
        $dto->comment_type = $comment_type;

        $dto->allow_comments = $this->campaign_settings->allow_comments();
        $dto->is_global_setting = true;

        return $dto;
    }

    /**
     * Get user data for a single user
     * 
     * @param int $user_id
     * @return array
     */
    public function get_user_data(int $user_id)
    {
        $user = growfund_user($user_id);
        if (!$user) {
            return [
                'display_name' => 'Unknown User',
                'user_login' => '',
                'avatar' => ''
            ];
        }

        $wp_user = $user->get();
        if (!$wp_user) {
            return [
                'display_name' => 'Unknown User',
                'user_login' => '',
                'avatar' => ''
            ];
        }

        $user_avatar = UserMeta::get($user_id, 'image');

        return [
            'display_name' => $this->get_user_display_name($wp_user),
            'user_login' => $wp_user->user_login ?? '',
            'avatar' => !empty($user_avatar) ? $user_avatar : ''
        ];
    }

    /**
     * Validate that a post exists
     * 
     * @param int $post_id
     * @throws Exception
     */
    protected function validate_post_exists(int $post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            throw new Exception(esc_html__('Invalid post.', 'growfund'));
        }
    }

    /**
     * Fetch comments from database
     * 
     * @param int $post_id
     * @param int $per_page
     * @param int $offset
     * @param string $comment_type
     * @return array
     */
    protected function fetch_comments(int $post_id, int $per_page, int $offset, string $comment_type)
    {
        $approved_comments = get_comments([
            'post_id' => $post_id,
            'status' => CommentConstants::STATUS_APPROVED,
            'parent' => CommentConstants::DEFAULT_PARENT_ID,
            'number' => $per_page,
            'offset' => $offset,
            'order' => 'DESC',
            'orderby' => 'comment_date',
            'comment_type' => $comment_type
        ]);

        if ($this->can_user_see_own_unapproved_comments($post_id)) {
            $current_user_id = growfund_user()->get_id();

            $user_unapproved_comments = get_comments([
                'post_id' => $post_id,
                'status' => 'hold',
                'parent' => CommentConstants::DEFAULT_PARENT_ID,
                'user_id' => $current_user_id,
                'comment_type' => $comment_type
            ]);

            $all_comments = array_merge($approved_comments, $user_unapproved_comments);
            usort($all_comments, function ($a, $b) {
                return strtotime($b->comment_date) - strtotime($a->comment_date);
            });

            return array_slice($all_comments, $offset, $per_page);
        }

        return $approved_comments;
    }

    /**
     * Create empty comment DTO
     * 
     * @param int $page
     * @param int $per_page
     * @return CommentDTO
     */
    protected function create_empty_comment_dto(int $page, int $per_page)
    {
        $dto = new CommentDTO([
            'comments' => [],
            'page' => $page,
            'per_page' => $per_page,
            'total' => CommentConstants::DEFAULT_TOTAL
        ]);

        $dto->current_page = $page;
        $dto->total_posts = CommentConstants::DEFAULT_TOTAL;
        $dto->total_pages = $per_page > 0 ? (int) ceil(CommentConstants::DEFAULT_TOTAL / $per_page) : 1;
        $dto->has_more = false;

        return $dto;
    }

    /**
     * Format comments with their replies
     * 
     * @param array $comments
     * @param string $comment_type
     * @return array
     */
    protected function format_comments_with_replies(array $comments, string $comment_type)
    {
        $comment_ids = wp_list_pluck($comments, 'comment_ID');
        $all_replies = $this->get_replies_for_comments($comment_ids, $comment_type);
        $all_comments_and_replies = array_merge($comments, array_merge(...array_values($all_replies)));
        $all_user_data = $this->get_user_data_for_comments($all_comments_and_replies);

        $formatted_comments = [];
        foreach ($comments as $comment) {
            $replies = $all_replies[$comment->comment_ID] ?? [];
            $dto = $this->prepare_comment_dto($comment, $replies, $all_user_data);
            if ($dto) {
                $formatted_comments[] = $dto;
            }
        }

        return $formatted_comments;
    }



    /**
     * Check if comments are enabled
     * 
     * @return bool
     */
    protected function check_comments_enabled()
    {
        return $this->campaign_settings->allow_comments();
    }

    /**
     * Check if comment form should be shown
     * 
     * @return bool
     */
    protected function check_comment_form_enabled()
    {
        if (!$this->can_user_post_campaign_comments()) {
            return false;
        }

        return true;
    }

    /**
     * Get current user information
     * 
     * @return array
     */
    public function get_current_user_info()
    {
        $is_logged_in = growfund_user()->get_id() > CommentConstants::USER_ID_CHECK_THRESHOLD;
        $name = '';
        $avatar = '';

        if ($is_logged_in) {
            $user_data = $this->get_user_data(growfund_user()->get_id());
            $name = $user_data['display_name'];
            $avatar = $user_data['avatar'];
        }

        return [
            'is_logged_in' => $is_logged_in,
            'name' => $name,
            'avatar' => $avatar
        ];
    }

    /**
     * Get user display name
     * 
     * @param object $wp_user
     * @return string
     */
    protected function get_user_display_name($wp_user)
    {
        if (!empty($wp_user->display_name)) {
            return $wp_user->display_name;
        }

        if (!empty($wp_user->user_login)) {
            return $wp_user->user_login;
        }

        if (!empty($wp_user->first_name) || !empty($wp_user->last_name)) {
            return trim($wp_user->first_name . ' ' . $wp_user->last_name);
        }

        return 'Anonymous';
    }

    /**
     * Get form texts
     * 
     * @param bool $is_reply
     * @param string $placeholder
     * @param string $button_text
     * @return array
     */
    protected function get_form_texts(bool $is_reply, string $placeholder, string $button_text)
    {
        if (empty($placeholder)) {
            $placeholder = $is_reply ? __('Write a reply...', 'growfund') : __('Share your thoughts, ask questions or show your support...', 'growfund');
        }

        if (empty($button_text)) {
            $button_text = $is_reply ? __('Reply', 'growfund') : __('Post Comment', 'growfund');
        }

        return [
            'placeholder' => $placeholder,
            'button_text' => $button_text
        ];
    }

    /**
     * Get single user data
     * 
     * @param int $user_id
     * @return array
     */
    protected function get_single_user_data(int $user_id)
    {
        $user_data = $this->get_user_data($user_id);
        return [
			$user_id => [
				'display_name' => $user_data['display_name'],
				'user_login' => $user_data['user_login']
			]
        ];
    }

    /**
     * Get replies for multiple comments in a single query
     * 
     * @param array $comment_ids
     * @param string $comment_type
     * @return array
     */
    protected function get_replies_for_comments(array $comment_ids, string $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE)
    {
        if (empty($comment_ids)) {
            return [];
        }

        $first_comment = get_comment($comment_ids[0]);
        $post_id = $first_comment ? $first_comment->comment_post_ID : 0;

        $approved_replies = get_comments([
            'parent__in' => $comment_ids,
            'status' => CommentConstants::STATUS_APPROVED,
            'order' => 'ASC',
            'orderby' => 'comment_date',
            'comment_type' => $comment_type
        ]);

        if ($this->can_user_see_own_unapproved_comments($post_id)) {
            $current_user_id = growfund_user()->get_id();

            $user_unapproved_replies = get_comments([
                'parent__in' => $comment_ids,
                'status' => 'hold',
                'user_id' => $current_user_id,
                'order' => 'ASC',
                'orderby' => 'comment_date',
                'comment_type' => $comment_type
            ]);

            $replies = array_merge($approved_replies, $user_unapproved_replies);
        } else {
            $replies = $approved_replies;
        }

        $grouped_replies = [];
        foreach ($replies as $reply) {
            $grouped_replies[$reply->comment_parent][] = $reply;
        }

        return $grouped_replies;
    }

    /**
     * Get user data for multiple comments in a single query
     * 
     * @param array $comments
     * @return array
     */
    protected function get_user_data_for_comments(array $comments)
    {
        $user_ids = Arr::make($comments)
            ->pluck('user_id')
            ->filter(function ($id) {
                return !empty($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->toArray();
        $user_ids = array_unique($user_ids);

        if (empty($user_ids)) {
            return [];
        }

        $users = QueryBuilder::query()
            ->table('users')
            ->select(['ID', 'display_name', 'user_login'])
            ->where_in('ID', $user_ids)
            ->get();

        $user_data = [];
        foreach ($users as $user) {
            $user_data[$user->ID] = [
                'display_name' => !empty($user->display_name) ? $user->display_name : $user->user_login,
                'user_login' => $user->user_login
            ];
        }

        return $user_data;
    }

    /**
     * Prepare template data for reply form
     *
     * @param int $post_id
     * @param int $comment_id
     * @param string $author_name
     * @param string $comment_type
     * @return CommentFormDTO
     */
    public function prepare_reply_form_template_data(int $post_id, int $comment_id, string $author_name, string $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE)
    {
        $user_info = $this->get_current_user_info();

        return new CommentFormDTO([
            'post_id' => $post_id,
            'comment_id' => $comment_id,
            'author_name' => $author_name,
            'comment_type' => $comment_type,
            'is_reply' => true,
            'placeholder' => __('Write a reply...', 'growfund'),
            'button_text' => __('Reply', 'growfund'),
            'reply_to_author' => $author_name,
            'parent_id' => $comment_id,
            'is_logged_in' => $user_info['is_logged_in'],
            'current_user_name' => $user_info['name'],
            'current_user_avatar' => $user_info['avatar']
        ]);
    }

    /**
     * Prepare template data for comment display
     *
     * @param CommentCardItemDTO $comment_dto
     * @param bool $is_reply
     * @param string $comment_type
     * @return array
     */
    public function prepare_comment_template_data(CommentCardItemDTO $comment_dto, bool $is_reply = false, string $comment_type = CommentConstants::DEFAULT_COMMENT_TYPE)
    {
        return [
            'comment' => $comment_dto,
            'nested' => $is_reply,
            'showMainWrapper' => false,
            'isAjaxResponse' => true,
            'comment_type' => $comment_type,
            'isThread' => false
        ];
    }

    /**
     * Prepare comment data into CommentCardItemDTO format
     * 
     * @param WP_Comment $comment
     * @param array $replies
     * @param array $all_user_data
     * @return CommentCardItemDTO
     */
    protected function prepare_comment_dto(WP_Comment $comment, array $replies = [], array $all_user_data = [])
    {
        $formatted_comment = $this->prepare_comment_base_dto($comment, $all_user_data);

        if (!$formatted_comment) {
            return null;
        }

        $formatted_replies = [];
        foreach ($replies as $reply) {
            $reply_dto = $this->prepare_reply_dto($reply, $all_user_data);
            if ($reply_dto) {
                $formatted_replies[] = $reply_dto;
            }
        }

        $formatted_comment->replies_count = count($formatted_replies);
        $formatted_comment->replies = $formatted_replies;

        return $formatted_comment;
    }

    /**
     * Prepare reply data into CommentCardItemDTO format (non-recursive)
     * 
     * @param WP_Comment $reply
     * @param array $all_user_data
     * @return CommentCardItemDTO
     */
    protected function prepare_reply_dto(WP_Comment $reply, array $all_user_data = [])
    {
        $dto = $this->prepare_comment_base_dto($reply, $all_user_data);

        if (!$dto) {
            return null;
        }

        $dto->replies_count = CommentConstants::DEFAULT_REPLIES_COUNT;
        $dto->replies = [];

        return $dto;
    }

    /**
     * Prepare base comment data into CommentCardItemDTO format (shared logic)
     * 
     * @param WP_Comment $comment
     * @param array $all_user_data
     * @return CommentCardItemDTO
     */
    protected function prepare_comment_base_dto(WP_Comment $comment, array $all_user_data = [])
    {
        if (!$this->can_user_see_comment($comment->comment_ID)) {
            return null;
        }

        $user_id = $comment->user_id;
        $author_name = $comment->comment_author;
        $author_image = '';

        if ($user_id && isset($all_user_data[$user_id])) {
            $author_name = $all_user_data[$user_id]['display_name'];
            $author_image = UserMeta::get($user_id, 'image');
        }

        $avatar = !empty($author_image) ? MediaAttachment::make($author_image) : '';
        $avatar = $avatar ? $avatar['url'] : '';

        $formatted_comment = new CommentCardItemDTO();
        $formatted_comment->id = $comment->comment_ID;
        $formatted_comment->author = $author_name;
        $formatted_comment->avatar = $avatar;
        $formatted_comment->time = Date::human_readable_time_diff($comment->comment_date);
        $formatted_comment->text = $comment->comment_content;
        $formatted_comment->parent_id = $comment->comment_parent;
        $formatted_comment->user_id = $comment->user_id;
        $formatted_comment->can_reply = $this->can_user_post_campaign_comments();
        $formatted_comment->can_delete = $this->can_user_delete_comment($comment, $user_id);
        $formatted_comment->comment_type = $comment->comment_type;

        $formatted_comment->is_awaiting_approval = ((int) $comment->comment_approved  === CommentConstants::COMMENT_UNAPPROVED_STATUS);

        return $formatted_comment;
    }

    /**
     * Check if the current user can see a specific comment
     * 
     * Evaluates user access to a specific comment based on:
     * - Comment approval status
     * - Campaign comment visibility rules
     * - User's relationship to the comment (author can see unapproved comments)
     *
     * @param int $comment_id The comment ID to check access for
     * @return bool True if user can see the comment, false otherwise
     */
    public function can_user_see_comment($comment_id)
    {
        $comment = get_comment($comment_id);

        if (!$comment) {
            return false;
        }

        if ((int) $comment->comment_approved === CommentConstants::COMMENT_APPROVED_STATUS) {
            return $this->can_user_see_campaign_comments($comment->comment_post_ID);
        }

        if ((int) $comment->comment_approved === CommentConstants::COMMENT_UNAPPROVED_STATUS) {
            return growfund_user()->is_logged_in() && (int) $comment->user_id === growfund_user()->get_id();
        }

        return false;
    }

    /**
     * Check if the current user can see their own unapproved comments for a specific post
     * 
     * Verifies user access to view their own pending comments on a specific post
     * by checking both comment visibility rules and user authentication.
     *
     * @param int $post_id The post ID to check comment access for
     * @return bool True if user can see their unapproved comments, false otherwise
     */
    public function can_user_see_own_unapproved_comments($post_id)
    {
        if (!growfund_user()->is_logged_in() || !$this->can_user_see_campaign_comments($post_id)) {
            return false;
        }

        return true;
    }

    /**
     * Check if user can see campaign comments
     * 
     * Evaluates user access to campaign comments based on:
     * - Global comment system status
     * - Comment visibility settings (public, logged-in users, contributors only)
     * - User's contribution status to the specific campaign (when applicable)
     * - Campaign update post handling (resolves parent campaign ID)
     *
     * @param int|null $campaign_id Optional campaign ID to check specific campaign access
     * @return bool True if user can see campaign comments, false otherwise
     */
    public function can_user_see_campaign_comments($campaign_id = null)
    {
        if (!$this->campaign_settings->allow_comments()) {
            return false;
        }

        $visibility = $this->campaign_settings->comment_visibility();

        if ($visibility === Visibility::PUBLIC) {
            return true;
        }

        if ($visibility === Visibility::LOGGED_IN_USERS) {
            return growfund_user()->is_logged_in();
        }

        if ($visibility === Visibility::CONTRIBUTORS) {
            if (!growfund_user()->is_logged_in()) {
                return false;
            }

            if ($campaign_id) {
                $actual_campaign_id = $this->get_actual_campaign_id($campaign_id);
                return (new CampaignService())->has_contribution($actual_campaign_id, growfund_user()->get_id());
            }

            return growfund_user()->is_backer();
        }

        return true;
    }

    /**
     * Get the actual campaign ID, handling campaign updates
     * 
     * Resolves the true campaign ID when dealing with campaign update posts.
     * Campaign updates are stored as separate posts with a parent relationship
     * to the main campaign, so this method traverses the hierarchy to find
     * the root campaign ID for permission checks.
     *
     * @param int $post_id The post ID to resolve (could be campaign or campaign update)
     * @return int The actual campaign ID (either the post ID itself or its parent campaign ID)
     */
    protected function get_actual_campaign_id($post_id)
    {
        $post = get_post($post_id);
        if (!$post) {
            return $post_id;
        }

        if ($post->post_type === Campaign::NAME) {
            if ($post->post_parent > 0) {
                return (int) $post->post_parent;
            }
        }

        return $post_id;
    }

    /**
     * Check if user can post comments on a campaign
     * 
     * Evaluates user permission to create new comments on campaigns based on:
     * - Global comment system status
     * - User authentication status
     * - Comment visibility restrictions (contributors-only mode)
     * - User's contribution status to the specific campaign (when applicable)
     *
     * @param int|null $campaign_id Optional campaign ID to check specific campaign permissions
     * @param int|null $user_id Optional user ID to check specific user permissions
     * @return bool True if user can post comments, false otherwise
     */
    public function can_user_post_campaign_comments($campaign_id = null, $user_id = null)
    {
        if (!$this->campaign_settings->allow_comments()) {
            return false;
        }

        if (!growfund_user()->is_logged_in()) {
            return false;
        }

        if ($user_id && $campaign_id && $this->campaign_settings->comment_visibility() === Visibility::CONTRIBUTORS) {
            $actual_campaign_id = $this->get_actual_campaign_id($campaign_id);
            return (new CampaignService())->has_contribution($actual_campaign_id, $user_id);
        }

        return true;
    }
}
