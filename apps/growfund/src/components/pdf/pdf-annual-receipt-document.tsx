import { Document, Page } from '@react-pdf/renderer';
import { __ } from '@wordpress/i18n';

import PdfFooter from '@/components/pdf/sections/pdf-footer';
import PdfHeader from '@/components/pdf/sections/pdf-header';
import PdfReceiptGreetings from '@/components/pdf/sections/pdf-receipt-greetings';
import PdfSignature from '@/components/pdf/sections/pdf-signature';
import { PdfProvider } from '@/contexts/pdf-context';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { type AppConfig } from '@/features/settings/schemas/settings';

const PdfAnnualReceiptDocument = ({
  pdfReceiptTemplate,
  children,
  toCurrency,
  appConfig,
}: React.PropsWithChildren<{
  pdfReceiptTemplate?: PdfReceiptTemplate;
  toCurrency: (amount: string | number, withSymbol?: boolean) => string;
  appConfig: AppConfig;
}>) => {
  return (
    <PdfProvider
      pdfReceiptTemplate={pdfReceiptTemplate}
      toCurrency={toCurrency}
      appConfig={appConfig}
    >
      <Document>
        <Page
          size="A4"
          style={{
            margin: 0,
            fontFamily: 'Helvetica',
            backgroundColor: pdfReceiptTemplate?.colors?.background ?? '#ffffff',
            color: pdfReceiptTemplate?.colors?.primary_text ?? '#333333',
            fontSize: 12,
            paddingTop: 12,
            paddingLeft: 48,
            paddingRight: 48,
            paddingBottom: 12,
          }}
        >
          <PdfHeader heading={__('Annual Receipt', 'growfund')} />
          <PdfReceiptGreetings />
          {children}
          {appConfig[AppConfigKeys.DonationMode] === '1' && <PdfSignature />}
          <PdfFooter />
        </Page>
      </Document>
    </PdfProvider>
  );
};

export default PdfAnnualReceiptDocument;
