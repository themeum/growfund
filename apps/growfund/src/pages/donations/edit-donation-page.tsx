import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useNavigate } from 'react-router';

import { DonationEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { Container } from '@/components/layouts/container';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { RouteConfig } from '@/config/route-config';
import DonationView from '@/features/donations/components/donation-view';
import { useDonationDetailsQuery } from '@/features/donations/services/donations';
import { useRouteParams } from '@/hooks/use-route-params';
import { DATE_FORMATS } from '@/lib/date';
import { matchQueryStatus } from '@/utils/match-query-status';

const EditDonationPage = () => {
  const navigate = useNavigate();

  const { id } = useRouteParams(RouteConfig.EditDonation);
  const donationDetailsQuery = useDonationDetailsQuery(id);

  return matchQueryStatus(donationDetailsQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <EmptyState>
        <EmptyStateDescription>
          <DonationEmptyStateIcon />
          {__('Error loading donation details', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Empty: (
      <EmptyState>
        <EmptyStateDescription>
          <DonationEmptyStateIcon />
          {__('No donation found', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data: donation }) => {
      return (
        <Page>
          <PageHeader
            name={
              <div className="growfund-flex growfund-flex-col growfund-justify-center">
                {/* translators: %s: donation id */}
                <span>{sprintf(__('Donation #%s', 'growfund'), donation.id)}</span>
                <span className="growfund-text-fg-primary growfund-typo-tiny growfund-font-regular">
                  {sprintf(
                    /* translator: %s: created date */
                    __('Placed on %s', 'growfund'),
                    format(new Date(donation.created_at), DATE_FORMATS.HUMAN_READABLE),
                  )}
                </span>
              </div>
            }
            onGoBack={() => navigate(RouteConfig.Donations.buildLink())}
          />
          <PageContent>
            <Container className="growfund-mt-10">
              <DonationView donation={donation} />
            </Container>
          </PageContent>
        </Page>
      );
    },
  });
};

export default EditDonationPage;
