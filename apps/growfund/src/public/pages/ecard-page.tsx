import { PDFViewer } from '@react-pdf/renderer';
import { __ } from '@wordpress/i18n';

import { DonationEmptyStateIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import { PdfProvider } from '@/contexts/pdf-context';
import { useCurrency } from '@/hooks/use-currency';
import { useRouteParams } from '@/hooks/use-route-params';
import EcardPDF from '@/public/components/ecard-pdf';
import { useECardQuery } from '@/public/services/donation';
import { matchQueryStatus } from '@/utils/match-query-status';

const EcardPage = () => {
  const { uid } = useRouteParams(RouteConfig.DonationReceipt);
  const { toCurrency } = useCurrency();
  const { appConfig } = useAppConfig();

  const eCardQuery = useECardQuery(uid);
  const windowHeight: number = window.innerHeight;

  return matchQueryStatus(eCardQuery, {
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
      const template = response.data.template;
      const donation = response.data.donation;
      return (
        <PDFViewer width="100%" height={windowHeight}>
          <PdfProvider toCurrency={toCurrency} appConfig={appConfig}>
            <EcardPDF template={template} donation={donation} />
          </PdfProvider>
        </PDFViewer>
      );
    },
  });
};

export default EcardPage;
