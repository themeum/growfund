import { __ } from '@wordpress/i18n';

import { DonationEmptyStateIcon, EmptySearchIcon, ErrorIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { LIST_LIMIT } from '@/constants/list-limits';
import DonationCard from '@/dashboards/donors/components/donation-card';
import { useDonationsQuery } from '@/features/donations/services/donations';
import useCurrentUser from '@/hooks/use-current-user';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const RecentDonations = () => {
  const { currentUser } = useCurrentUser();
  const donationsQuery = useDonationsQuery({
    per_page: LIST_LIMIT.DONOR_RECENT_DONATIONS,
    user_id: currentUser.id,
    orderby: 'created_at',
    order: 'DESC',
  });

  return matchPaginatedQueryStatus(donationsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading donations', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <DonationEmptyStateIcon />
        <EmptyStateDescription>{__('No donations made yet.', 'growfund')}</EmptyStateDescription>
      </EmptyState>
    ),
    EmptyResult: (
      <EmptyState className="growfund-mt-10">
        <EmptySearchIcon />
        <EmptyStateDescription>
          {__('No matching results found.', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }, emptyResult) => {
      return (
        <div className="growfund-space-y-4">
          <div className="growfund-flex growfund-items-center">
            <h4 className="growfund-typo-h5 growfund-font-semibold growfund-text-primary">
              {__('Recent donations', 'growfund')}
            </h4>
          </div>
          <div className="growfund-space-y-3">
            {isDefined(emptyResult)
              ? emptyResult
              : data.results.map((donation) => (
                  <DonationCard key={donation.id} donation={donation} />
                ))}
          </div>
        </div>
      );
    },
  });
};

export default RecentDonations;
