import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

import { EmptySearchIcon, ErrorIcon, PledgeEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import Paginator from '@/components/ui/paginator';
import { LIST_LIMIT } from '@/constants/list-limits';
import { type FilterForm } from '@/dashboards/backers/components/backer-pledges-page-content';
import PledgeCardSkeleton from '@/dashboards/backers/features/overview/components/skeletons/pledge-card-skeleton';
import PledgeItem from '@/dashboards/backers/features/pledges/components/pledge-item';
import { usePledgesQuery } from '@/features/pledges/services/pledges';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

interface PledgesPageContentProps {
  onLoadData: (total: number) => void;
}

const PledgesPageContent = ({ onLoadData }: PledgesPageContentProps) => {
  const { currentUser, isFundraiser } = useCurrentUser();
  const form = useFormContext<FilterForm>();

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
      search: formData.search,
      start_date: formData.date_range?.from
        ? format(formData.date_range.from, DATE_FORMATS.DATE)
        : undefined,
      end_date: formData.date_range?.to
        ? format(formData.date_range.to, DATE_FORMATS.DATE)
        : undefined,
    }),
    watchFields: ['search', 'date_range'],
  });

  const pledgesQuery = usePledgesQuery({
    page: params.pg,
    search: params.search ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
    user_id: isFundraiser ? currentUser.id : undefined,
    per_page: LIST_LIMIT.BACKER_PLEDGES,
  });

  useEffect(() => {
    onLoadData(pledgesQuery.data?.total ?? 0);
  }, [pledgesQuery.data?.total, onLoadData]);

  return matchPaginatedQueryStatus(pledgesQuery, {
    Loading: (
      <div className="growfund-space-y-4">
        {Array.from({ length: 5 }).map((_, index) => (
          <PledgeCardSkeleton key={index} />
        ))}
      </div>
    ),
    Error: (
      <ErrorState>
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading pledges', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState>
        <PledgeEmptyStateIcon />
        <EmptyStateDescription>{__('No pledges made yet', 'growfund')}</EmptyStateDescription>
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
            {data.results.map((pledge) => {
              return <PledgeItem pledge={pledge} key={pledge.id} />;
            })}
          </div>

          <div className="growfund-mt-3">
            <Paginator
              totalItems={data.total}
              currentPage={data.current_page}
              itemsPerPage={data.per_page}
              onPageChange={(page) => {
                syncQueryParams({ pg: page });
              }}
            />
          </div>
        </>
      );
    },
  });
};

export default PledgesPageContent;
