import { __ } from '@wordpress/i18n';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

import { CampaignEmptyStateIcon, EmptySearchIcon, ErrorIcon } from '@/app/icons';
import CampaignCard from '@/components/campaigns/campaign-card';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import Paginator from '@/components/ui/paginator';
import { LIST_LIMIT } from '@/constants/list-limits';
import CampaignCardSkeleton from '@/dashboards/backers/features/overview/components/skeletons/campaign-card-skeleton';
import {
    useBookmarksQuery,
    useRemoveBookmarkMutation,
} from '@/dashboards/shared/services/bookmark';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const BookmarkedCampaigns = ({
  onLoadData,
  userId,
}: {
  onLoadData: (data: { total: number; overall: number }) => void;
  userId: string | undefined;
}) => {
  const form = useFormContext<{ search?: string }>();

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      page: params.pg,
      search: params.search ?? undefined,
    }),
    formToParams: (formData) => ({
      search: formData.search ?? null,
    }),
    watchFields: ['search'],
  });

  const removeBookmarkMutation = useRemoveBookmarkMutation();

  const bookmarksQuery = useBookmarksQuery({
    user_id: userId,
    page: params.pg,
    search: params.search ?? undefined,
    per_page: LIST_LIMIT.USER_BOOKMARKS,
  });

  useEffect(() => {
    onLoadData({
      total: bookmarksQuery.data?.total ?? 0,
      overall: bookmarksQuery.data?.overall ?? 0,
    });
  }, [bookmarksQuery.data?.total, bookmarksQuery.data?.overall, onLoadData]);

  return matchPaginatedQueryStatus(bookmarksQuery, {
    Loading: (
      <div className="growfund-space-y-3">
        {Array.from({ length: 5 }).map((_, index) => (
          <CampaignCardSkeleton key={index} />
        ))}
      </div>
    ),
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading campaigns', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <CampaignEmptyStateIcon />
        <EmptyStateDescription>
          {__('No campaigns bookmarked yet', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    EmptyResult: (
      <EmptyState>
        <EmptySearchIcon />
        <EmptyStateDescription>
          {__('No matching results found.', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }, emptyResult) => {
      if (isDefined(emptyResult)) {
        return emptyResult;
      }
      return (
        <>
          <div className="growfund-space-y-3">
            {data.results.map((campaign) => {
              return (
                <CampaignCard
                  key={campaign.id}
                  campaign={campaign}
                  className="growfund-border-none growfund-shadow-none"
                  mode="bookmark"
                  onRemoveBookmark={() => {
                    if (!userId || !campaign.id) {
                      return;
                    }
                    removeBookmarkMutation.mutate({
                      user_id: userId,
                      campaign_id: campaign.id,
                    });
                  }}
                />
              );
            })}
          </div>

          <Paginator
            currentPage={data.current_page}
            totalItems={data.total}
            itemsPerPage={data.per_page}
            onPageChange={(page) => {
              syncQueryParams({ pg: page });
            }}
          />
        </>
      );
    },
  });
};

export default BookmarkedCampaigns;
