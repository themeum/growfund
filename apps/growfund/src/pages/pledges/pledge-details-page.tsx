import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useNavigate } from 'react-router';

import { PledgeEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Container } from '@/components/layouts/container';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { RouteConfig } from '@/config/route-config';
import PledgeDetails from '@/features/pledges/components/pledge-details';
import { usePledgeDetailsQuery } from '@/features/pledges/services/pledges';
import { useRouteParams } from '@/hooks/use-route-params';
import { DATE_FORMATS } from '@/lib/date';
import { matchQueryStatus } from '@/utils/match-query-status';

const EditPledgePage = () => {
  const navigate = useNavigate();

  const { id } = useRouteParams(RouteConfig.EditPledge);
  const pledgeDetailsQuery = usePledgeDetailsQuery(id);

  return matchQueryStatus(pledgeDetailsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <EmptyState>
        <EmptyStateDescription>
          <PledgeEmptyStateIcon />
          {__('Error loading pledge details', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Empty: (
      <EmptyState>
        <EmptyStateDescription>
          <PledgeEmptyStateIcon />
          {__('No pledge found.', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      return (
        <Page>
          <PageHeader
            name={
              <div className="growfund-flex growfund-flex-col growfund-justify-center">
                {/* translators: %s: pledge id */}
                <span>{sprintf(__('Pledge #%s', 'growfund'), data.id)}</span>
                <span className="growfund-text-fg-primary growfund-typo-tiny growfund-font-regular">
                  {sprintf(
                    /* translator: %s: created date */
                    __('Placed on %s', 'growfund'),
                    format(new Date(data.created_at), DATE_FORMATS.HUMAN_READABLE),
                  )}
                </span>
              </div>
            }
            onGoBack={() => navigate(RouteConfig.Pledges.buildLink())}
          />

          <PageContent>
            <Container className="growfund-mt-10">
              <PledgeDetails pledge={data} />
            </Container>
          </PageContent>
        </Page>
      );
    },
  });
};

export default EditPledgePage;
