import { __ } from '@wordpress/i18n';
import { useNavigate } from 'react-router';

import { EmptySearchIcon, ErrorIcon, PledgeEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { LIST_LIMIT } from '@/constants/list-limits';
import PledgeCardSkeleton from '@/dashboards/backers/features/overview/components/skeletons/pledge-card-skeleton';
import PledgeList from '@/dashboards/backers/features/pledges/components/pledge-list';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import { usePledgesQuery } from '@/features/pledges/services/pledges';
import { isDefined } from '@/utils';
import { matchPaginatedQueryStatus } from '@/utils/match-paginated-query-status';

const BackerRecentPledges = () => {
  const navigate = useNavigate();
  const pledgesQuery = usePledgesQuery({
    per_page: LIST_LIMIT.BACKER_RECENT_PLEDGES,
  });

  return matchPaginatedQueryStatus(pledgesQuery, {
    Loading: (
      <div className="growfund-space-y-4">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <Skeleton className="growfund-h-3 growfund-w-[10rem]" animate />
          <Skeleton className="growfund-h-9 growfund-w-[8rem]" animate />
        </div>
        <div className="growfund-space-y-4">
          {Array.from({ length: 5 }).map((_, index) => (
            <PledgeCardSkeleton key={index} />
          ))}
        </div>
      </div>
    ),
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>{__('Error loading pledges', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <PledgeEmptyStateIcon />
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          {__('No pledges made yet', 'growfund')}
        </EmptyStateDescription>
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
      if (isDefined(emptyResult)) {
        return emptyResult;
      }
      return (
        <div className="growfund-space-y-4">
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <h5 className="growfund-typo-h5 growfund-text-fg-primary">
              {__('Recent Pledges', 'growfund')}
            </h5>
            <Button
              variant="outline"
              onClick={() => {
                void navigate(UserRouteConfig.Pledges.buildLink());
              }}
            >
              {__('See All Pledges', 'growfund')}
            </Button>
          </div>

          <div className="growfund-mb-4">
            <PledgeList pledges={data.results} />
          </div>
        </div>
      );
    },
  });
};

export default BackerRecentPledges;
