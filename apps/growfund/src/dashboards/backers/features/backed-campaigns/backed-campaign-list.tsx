import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

import { CampaignEmptyStateIcon, EmptySearchIcon, ErrorIcon } from '@/app/icons';
import CampaignCard from '@/components/campaigns/campaign-card';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import Paginator from '@/components/ui/paginator';
import CampaignCardSkeleton from '@/dashboards/backers/features/overview/components/skeletons/campaign-card-skeleton';
import { useCampaignsByBackerQuery } from '@/features/backers/services/backer';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const BackedCampaignList = ({ onLoadData }: { onLoadData: (total: number) => void }) => {
  const { currentUser: user } = useCurrentUser();
  const form = useFormContext<{ search: string; date_range?: { from?: Date; to?: Date } }>();

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
      start_date: parseAsString,
      end_date: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      search: params.search ?? '',
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search || undefined,
      start_date: formData.date_range?.from
        ? format(formData.date_range.from, DATE_FORMATS.DATE)
        : undefined,
      end_date: formData.date_range?.to
        ? format(formData.date_range.to, DATE_FORMATS.DATE)
        : undefined,
    }),
    watchFields: ['search', 'date_range'],
  });

  const backedCampaignsQuery = useCampaignsByBackerQuery({
    backer_id: user.id,
    page: params.pg,
    search: params.search ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
  });

  useEffect(() => {
    onLoadData(backedCampaignsQuery.data?.total ?? 0);
  }, [backedCampaignsQuery.data?.total, onLoadData]);

  return matchPaginatedQueryStatus(backedCampaignsQuery, {
    Loading: (
      <div className="growfund-space-y-4">
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
        <EmptyStateDescription>{__('No campaigns backed yet', 'growfund')}</EmptyStateDescription>
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
                  mode="view"
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

export default BackedCampaignList;
