<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\App\Events\CampaignPostUpdateEvent;
use Growfund\PostTypes\CampaignPost;
use Growfund\Sanitizer;
use Exception;
use Growfund\Constants\Tables;
use Growfund\Constants\WP;
use Growfund\DTO\CampaignPost\CampaignPostDTO;
use Growfund\DTO\CampaignPost\CampaignPostFilterDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\QueryBuilder;
use Growfund\Supports\MediaAttachment;
use Growfund\Supports\Pagination;
use Growfund\Supports\User;
use WP_Post;
use WP_Query;

/**
 * CampaignPostService class
 * @since 1.0.0
 */
class CampaignPostService
{
    public function paginated(CampaignPostFilterDTO $dto)
    {
        $args = [
            'post_type'      => CampaignPost::NAME,
            'paged'          => $dto->page,
            'posts_per_page' => $dto->limit,
            'orderby'        => $dto->orderby,
            'order'          => $dto->order,
            'post_parent'    => $dto->campaign_id,
            'no_found_rows'  => false,
            'cache_results'  => false,
        ];

        $query = new WP_Query($args);

        $results = [];

        if ($query->have_posts()) {
            foreach ($query->posts as $campaign_update) {
                $results[] = $this->prepare_campaign_update_dto($campaign_update);
            }
        }

        return PaginatedCollectionDTO::from_array([
            'results' => $results,
            'total' => $query->found_posts,
            'count' => count($results),
            'per_page' => $dto->limit,
            'current_page' => $dto->page,
            'has_more' => $dto->page < $query->max_num_pages,
            'overall' => Pagination::get_overall_post_count(CampaignPost::NAME),
        ]);
    }

    public function get_by_id(int $id) 
    {
		$campaign_update = get_post($id);

        if (!$campaign_update || $campaign_update->post_type !== CampaignPost::NAME) {
            /* translators: %s: campaign id */
            throw new Exception(sprintf(esc_html__('Campaign update with ID %s not found.', 'growfund'), esc_html($id)));
        }

        return $this->prepare_campaign_update_dto($campaign_update);
    }

    /**
     * Retrieves the adjacent (previous and next) post IDs for a specific update.
     */
    public function get_neighbors(CampaignPostDTO $post) 
    {
        $prev = QueryBuilder::query()
            ->table(WP::POSTS_TABLE)
            ->select(['ID as id'])
            ->where('post_type', CampaignPost::NAME)
            ->where('post_status', CampaignPost::DEFAULT_POST_STATUS)
            ->where('post_parent', $post->campaign_id)
            ->where('ID', '<', $post->id)
            ->order_by('ID', 'DESC')
            ->limit(1)
            ->first();

		$next = QueryBuilder::query()
            ->table(WP::POSTS_TABLE)
            ->select(['ID as id'])
            ->where('post_type', CampaignPost::NAME)
            ->where('post_status', CampaignPost::DEFAULT_POST_STATUS)
            ->where('post_parent', (int) $post->campaign_id)
            ->where('ID', '>', (int) $post->id)
            ->order_by('ID', 'ASC')
            ->limit(1)
            ->first();

        $prev_id = $prev ? $prev->id : 0;
		$next_id = $next ? $next->id : 0;

		return [
			'prev_id' => $prev_id,
			'next_id' => $next_id,
		];
	}

    protected function prepare_campaign_update_dto(WP_Post $campaign_update) 
    {
        $dto = new CampaignPostDTO();

        $image_id = get_post_thumbnail_id($campaign_update->ID);

        $author = growfund_user($campaign_update->post_author);

        $dto->id = (string) $campaign_update->ID;
        $dto->campaign_id = (string) $campaign_update->post_parent;
        $dto->title = $campaign_update->post_title;
        $dto->slug = $campaign_update->post_name;
        $dto->description = $campaign_update->post_content;
        $dto->image = MediaAttachment::make($image_id);
        $dto->created_by_id = $author->get_id();
        $dto->created_by_name = $author->get_display_name();
        $dto->created_by_role = $author->get_active_role();
        $dto->created_by_image = User::get_avatar_image($image_id) ?? [
            'url' => growfund_user_avatar()
        ];
        $dto->created_at = $campaign_update->post_date_gmt;
        $dto->likes = 0; // @todo: will be added later

        return $dto;
    }


    /**
     * Create new campaign post update.
     * 
     * @param int $campaign_id
     * @param array $data
     * 
     * @return int $post_id
     */
    public function save(int $campaign_id, array $data)
    {
        $post_id = wp_insert_post([
            'post_type'    => CampaignPost::NAME,
            'post_title'   => $data['title'],
            'post_name'    => $data['slug'] ?? Sanitizer::apply_rule($data['title'], Sanitizer::TITLE),
            'post_content' => $data['description'] ?? '',
            'post_status'  => CampaignPost::DEFAULT_POST_STATUS,
            'post_author'  => get_current_user_id(),
            'post_parent'  => $campaign_id,
        ], true);

        if (is_wp_error($post_id)) {
            throw new Exception(esc_html($post_id->get_error_message()));
        }

        $image = $data['image'][0] ?? null;
        set_post_thumbnail($post_id, $image);

        growfund_event(new CampaignPostUpdateEvent($campaign_id, $post_id));

        return $post_id;
    }

    /**
     * Delete campaign post by parent id
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete_by_parent_id(int $id)
    {
        $posts = get_posts([
            'post_type' => CampaignPost::NAME,
            'post_parent' => $id,
            'numberposts' => -1,
            'post_status' => 'any',
        ]);

        if (empty($posts)) {
            return true;
        }

        foreach ($posts as $item) {
            wp_delete_post($item->ID, true);
        }

        return true;
    }
}
