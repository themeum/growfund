import { __ } from '@wordpress/i18n';

import { DonationEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { RouteConfig } from '@/config/route-config';
import { useRouteParams } from '@/hooks/use-route-params';
import DonationReceiptPdf from '@/public/components/donation-receipt-pdf';
import { useDonationReceiptQuery } from '@/public/services/donation';
import { matchQueryStatus } from '@/utils/match-query-status';

const DonationReceiptPage = () => {
  const { uid } = useRouteParams(RouteConfig.DonationReceipt);

  const donationReceiptQuery = useDonationReceiptQuery(uid);

  return matchQueryStatus(donationReceiptQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>
          <DonationEmptyStateIcon />
          <div>{__('Donation not found.', 'growfund')}</div>
        </ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          <DonationEmptyStateIcon />
          <div>{__('Donation not found.', 'growfund')}</div>
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: (response) => {
      return <DonationReceiptPdf {...response.data} />;
    },
  });
};

export default DonationReceiptPage;
