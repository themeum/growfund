import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

import { ErrorIcon } from '@/app/icons';
import CampaignCard from '@/components/campaigns/campaign-card';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Box, BoxContent } from '@/components/ui/box';
import { Form } from '@/components/ui/form';
import UserPreviewCard from '@/components/users/user-preview-card';
import { useCampaignDetailsQuery } from '@/features/campaigns/services/campaign';
import DonationAction from '@/features/donations/components/donation-action/donation-action';
import DonationPaymentViewCard from '@/features/donations/components/donation-payment-view-card';
import DonationTributeCard from '@/features/donations/components/donation-tribute';
import PaymentView from '@/features/donations/components/payment-view';
import DonationTimeline from '@/features/donations/components/timeline/donation-timeline';
import { type Donation, type DonationStatus } from '@/features/donations/schemas/donation';
import { isDefined } from '@/utils';
import { matchQueryStatus } from '@/utils/match-query-status';

const DonationView = ({ donation }: { donation: Donation }) => {
  const form = useForm<{ status: DonationStatus }>({
    defaultValues: {
      status: donation.status,
    },
  });

  const campaignDetailQuery = useCampaignDetailsQuery(donation.campaign.id);

  const hasTribute = isDefined(donation.tribute_type);

  return matchQueryStatus(campaignDetailQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorIcon />
        <ErrorStateDescription>
          {__('Error loading donation details', 'growfund')}
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          {__('No donation found.', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data: campaign }) => {
      return (
        <Form {...form}>
          <div className="growfund-grid growfund-grid-cols-[auto_20rem] growfund-gap-4">
            <div>
              <CampaignCard campaign={campaign} mode="view" />
              <div className="growfund-flex growfund-flex-col growfund-gap-4 growfund-mt-4">
                <DonationPaymentViewCard donation={donation} />
                <PaymentView
                  amount={donation.amount}
                  donationStatus={donation.status}
                  payment_method={donation.payment_method}
                />
                <DonationTimeline donationId={donation.id} />
              </div>
            </div>

            <div className="growfund-space-y-4">
              <DonationAction donation={donation} />
              <UserPreviewCard user={donation.donor} title={__('Donor', 'growfund')} />

              {hasTribute && (
                <Box>
                  <BoxContent>
                    <DonationTributeCard donation={donation} />
                  </BoxContent>
                </Box>
              )}

              <Box>
                <BoxContent>
                  <h6 className="growfund-typo-h6 growfund-text-fg-primary">
                    {__('Notes', 'growfund')}
                  </h6>
                  <div className="growfund-typo-small growfund-text-fg-secondary growfund-mt-3">
                    {donation.notes ?? __('No notes', 'growfund')}
                  </div>
                </BoxContent>
              </Box>
            </div>
          </div>
        </Form>
      );
    },
  });
};

export default DonationView;
