import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';
import { parseAsInteger, parseAsString } from 'nuqs';
import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

import { DonationEmptyStateIcon, EmptySearchIcon, ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import Paginator from '@/components/ui/paginator';
import { LIST_LIMIT } from '@/constants/list-limits';
import DonationCard from '@/dashboards/donors/components/donation-card';
import { DonationStatusSchema } from '@/features/donations/schemas/donation';
import { useDonationsQuery } from '@/features/donations/services/donations';
import useCurrentUser from '@/hooks/use-current-user';
import { useFormQuerySync } from '@/hooks/use-form-query-sync';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const DonationList = ({
  onLoadData,
}: {
  onLoadData: (data: { total: number; overall: number }) => void;
}) => {
  const { currentUser, isFundraiser } = useCurrentUser();

  const form = useFormContext<{
    search?: string;
    status?: string;
    date_range?: { from?: Date; to?: Date };
  }>();

  const { syncQueryParams, params } = useFormQuerySync({
    keyMap: {
      pg: parseAsInteger.withDefault(1),
      search: parseAsString,
      status: parseAsString,
      start_date: parseAsString,
      end_date: parseAsString,
    },
    form,
    paramsToForm: (params) => ({
      page: params.pg,
      search: params.search ?? undefined,
      status: params.status ?? undefined,
      date_range: {
        from: params.start_date ? new Date(params.start_date) : undefined,
        to: params.end_date ? new Date(params.end_date) : undefined,
      },
    }),
    formToParams: (formData) => ({
      search: formData.search || null,
      status: formData.status || null,
      start_date: formData.date_range?.from
        ? format(formData.date_range.from, DATE_FORMATS.DATE)
        : undefined,
      end_date: formData.date_range?.to
        ? format(formData.date_range.to, DATE_FORMATS.DATE)
        : undefined,
    }),
    watchFields: ['search', 'date_range', 'status'],
  });

  const donationsQuery = useDonationsQuery({
    page: params.pg,
    per_page: LIST_LIMIT.DONOR_DONATIONS,
    search: params.search ?? undefined,
    status: DonationStatusSchema.safeParse(params.status).data ?? undefined,
    start_date: params.start_date ?? undefined,
    end_date: params.end_date ?? undefined,
    user_id: isFundraiser ? currentUser.id : undefined,
  });

  useEffect(() => {
    onLoadData({
      total: donationsQuery.data?.total ?? 0,
      overall: donationsQuery.data?.overall ?? 0,
    });
  }, [donationsQuery.data?.total, donationsQuery.data?.overall, onLoadData]);

  return matchPaginatedQueryStatus(donationsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-px-0">
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading donations', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-px-0">
        <DonationEmptyStateIcon />
        <EmptyStateDescription>{__('No donations made yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    EmptyResult: (
      <EmptyState className="growfund-px-0">
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
        <div className="growfund-mt-4 growfund-space-y-3">
          <div className="growfund-space-y-4">
            {data.results.map((donation) => {
              return <DonationCard key={donation.id} donation={donation} />;
            })}
          </div>
          <Paginator
            totalItems={data.total}
            onPageChange={(page) => {
              syncQueryParams({ pg: page });
            }}
            currentPage={data.current_page}
            itemsPerPage={data.per_page}
          />
        </div>
      );
    },
  });
};

export default DonationList;
