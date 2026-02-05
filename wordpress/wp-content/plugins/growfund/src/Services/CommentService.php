<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Exception;
use Growfund\Constants\Comment\CommentModeration;
use Growfund\Core\AppSettings;
use Growfund\DTO\Comment\CommentDTO;
use Growfund\DTO\Comment\CommentFilterDTO;
use Growfund\DTO\Comment\CreateCommentDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\PostTypes\Campaign;
use Growfund\Supports\User;
use WP_Comment;

/**
 * CommentService class
 * @since 1.0.0
 */
class CommentService
{
    public function paginated(CommentFilterDTO $dto) {
        $args = [
            'post_id' => $dto->post_id,
            'paged' => $dto->page,
            'number' => $dto->limit,
            'orderby' => $dto->orderby,
            'order' => $dto->order,
            'parent' => $dto->parent,
            'status' => $dto->status,
            'type' => $dto->comment_type,
        ];

        $comments = get_comments($args);

        $results = [];

        foreach ($comments as $comment) {
            $results[] = $this->prepare_comment_dto($comment, $dto->status);
		}

        $total = $this->total_count($dto);

        $dto->status = 'all';
        $overall = $this->total_count($dto);

        return PaginatedCollectionDTO::from_array([
            'results' => $results,
            'total' => $total,
            'count' => count($results),
            'per_page' => $dto->limit,
            'current_page' => $dto->page,
            'has_more' => $dto->page * $dto->limit < $total,
            'overall' => $overall,
        ]);
    }

    public function total_count(CommentFilterDTO $dto) {
        $args = [
            'post_id' => $dto->post_id,
            'parent' => $dto->parent,
            'status' => $dto->status,
            'count' => true,
            'comment_type' => $dto->comment_type
        ];

        return get_comments($args);
    }

    protected function prepare_comment_dto(WP_Comment $comment, string $status = 'all') {
        $dto = new CommentDTO();

        $dto->id = (string) $comment->comment_ID;
        $dto->post_id = $comment->comment_post_ID;
        $dto->comment_parent = $comment->comment_parent;
        $dto->content = $comment->comment_content;
        $dto->author_id = $comment->user_id;
        $dto->author_name = $comment->comment_author;
        $dto->author_image = $comment->comment_author_url;
        $dto->created_at = $comment->comment_date_gmt;
        $dto->comment_approved = (bool) $comment->comment_approved;
        $dto->comment_type = $comment->comment_type;

        if (!$comment->comment_parent) {
			$filter_dto = new CommentFilterDTO();
			$filter_dto->parent = $comment->comment_ID;
			$filter_dto->post_id = $comment->comment_post_ID;
			$filter_dto->status = $status;
			$filter_dto->comment_type = $comment->comment_type;

			$dto->replies = $this->paginated($filter_dto);
        } else {
            $dto->exclude(['replies']);
        }
        

        return $dto;
    }

    public function store(CreateCommentDTO $dto) {
        $user = growfund_user();
                    
        $comment_data = [
            'comment_post_ID' => $dto->post_id ?? 0,
            'comment_content' => trim(wp_kses_post($dto->content ?? '')),
            'comment_parent' => $dto->parent_id ?? 0,
            'comment_type' => $dto->comment_type ?? Campaign::COMMENT_TYPE,
            'comment_agent' => 'growfund',
            'user_id' => $user->get_id(),
            'comment_author' => $user->get_display_name(),
            'comment_author_email' => $user->get_email(),
            'comment_author_url' => User::get_avatar_image($user->get_id())['url'] ?? '',
            'comment_approved' => growfund_settings(AppSettings::CAMPAIGNS)->comment_visibility() === CommentModeration::NEED_APPROVAL ? 0 : 1,
        ];

        $comment_id = wp_insert_comment($comment_data);

        if (!$comment_id) {
            throw new Exception(esc_html__('Failed to create comment.', 'growfund'));
        }

        return $comment_id;
    }

    public function get_by_id(int $commentId) {
		$comment = get_comment($commentId);

        if (!$comment) {
            throw new Exception(esc_html__('Comment not found.', 'growfund'));
        }

        return $this->prepare_comment_dto($comment);
    }

    public function approve(int $commentId)
    {
        return (bool) wp_update_comment([
            'comment_ID' => $commentId,
            'comment_approved' => 1,
        ]);
    }

    public function update(int $commentId, string $content): bool
    {
        $comment = get_comment($commentId);

        if (!$comment) {
            throw new Exception(esc_html__('Comment not found.', 'growfund'));
        }

        if ((int) $comment->user_id !== get_current_user_id()) {
            throw new Exception(esc_html__('You cannot edit this comment.', 'growfund'));
        }

        return (bool) wp_update_comment([
            'comment_ID'      => $commentId,
            'comment_content' => wp_kses_post($content),
        ]);
    }

    /**
     * Delete a comment
     */
    public function deleteComment(int $commentId, bool $force = false): bool
    {
        $comment = get_comment($commentId);

        if (!$comment) {
            throw new Exception(esc_html__('Comment not found.', 'growfund'));
        }

        if ((int) $comment->user_id !== get_current_user_id() && !current_user_can('moderate_comments')) {
            throw new Exception(esc_html__('You cannot delete this comment.', 'growfund'));
        }

        return wp_delete_comment($commentId, $force);
    }
}
