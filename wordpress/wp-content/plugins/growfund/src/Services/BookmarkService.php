<?php

namespace Growfund\Services;

defined( 'ABSPATH' ) || exit;

use Growfund\Constants\Tables;
use Growfund\DTO\Campaign\BookmarkFilterDTO;
use Growfund\DTO\Campaign\CampaignFiltersDTO;
use Growfund\DTO\PaginatedCollectionDTO;
use Growfund\Exceptions\QueryException;
use Growfund\QueryBuilder;
use Growfund\Supports\Arr;
use Growfund\Supports\Date;

class BookmarkService
{
    /**
     * Get the paginated bookmarked campaigns for a specific user.
     *
     * @param BookmarkFilterDTO $dto
     * @return PaginatedCollectionDTO
     */
    public function paginated(BookmarkFilterDTO $dto)
    {
        $campaign_ids = QueryBuilder::query()
            ->table(Tables::BOOKMARKS)
            ->select(['campaign_id'])
            ->where('user_id', $dto->user_id)
            ->get();

        $campaign_ids = Arr::make($campaign_ids)->pluck('campaign_id')->toArray();

        if (empty($campaign_ids)) {
            return PaginatedCollectionDTO::defaults();
        }

        $campaign_service = new CampaignService();

        $campaign_dto = new CampaignFiltersDTO();
        $campaign_dto->post_ids = $campaign_ids;
        $campaign_dto->search = $dto->search;
        $campaign_dto->limit = $dto->limit;
        $campaign_dto->page = $dto->page;

        return $campaign_service->paginated($campaign_dto);
    }

    /**
     * Bookmarking a campaign for a specific user.
     *
     * @param int $user_id
     * @param int $campaign_id
     *
     * @return bool True if the bookmark was created, false otherwise.
     *
     * @throws QueryException If any insertion error occurs.
     */
    public function create_bookmark(int $user_id, int $campaign_id)
    {
        return QueryBuilder::query()
            ->table(Tables::BOOKMARKS)
            ->create([
                'user_id' => $user_id,
                'campaign_id' => $campaign_id,
                'created_at' => Date::current_sql_safe(),
            ]);
    }

    /**
     * Remove a bookmarked campaign from the database.
     *
     * @param int $user_id The user ID to remove the bookmark for.
     * @param int $campaign_id The campaign ID to remove the bookmark for.
     * @return bool True if the bookmark was removed, false otherwise.
     *
     * @throws QueryException If the bookmark ID is invalid or any deletion error occurs.
     */
    public function remove_bookmark(int $user_id, int $campaign_id)
    {
        $bookmark = QueryBuilder::query()
            ->table(Tables::BOOKMARKS)
            ->where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->first();

        if (!$bookmark) {
            throw new QueryException(
                /* translators: 1: user ID, 2: campaign ID */
                sprintf(esc_html__('No bookmark found for user ID %1$s and campaign ID %2$s', 'growfund'), esc_html($user_id), esc_html($campaign_id))
            );
        }

        return QueryBuilder::query()
            ->table(Tables::BOOKMARKS)
            ->where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->delete();
    }

    /**
     * Check if a campaign is bookmarked by a specific user.
     *
     * @param int $user_id The user ID to check.
     * @param int $campaign_id The campaign ID to check.
     * @return bool True if the campaign is bookmarked, false otherwise.
     */
    public function is_bookmarked(int $user_id, int $campaign_id)
    {
        $bookmark = QueryBuilder::query()
            ->table(Tables::BOOKMARKS)
            ->where('user_id', $user_id)
            ->where('campaign_id', $campaign_id)
            ->first();

        return !empty($bookmark);
    }
}
