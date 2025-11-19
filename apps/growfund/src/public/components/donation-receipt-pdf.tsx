import { PDFViewer } from '@react-pdf/renderer';
import { sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useMemo } from 'react';

import PdfDonationContent from '@/components/pdf/contents/pdf-donation-content';
import PdfReceiptDocument from '@/components/pdf/pdf-receipt-document';
import { useAppConfig } from '@/contexts/app-config';
import { type Donation } from '@/features/donations/schemas/donation';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { shortcodeReplacement } from '@/lib/utils';
import { emptyCell } from '@/utils';

const DonationReceiptPdf = ({
  donation,
  template,
}: {
  donation: Donation;
  template: PdfReceiptTemplate;
}) => {
  const { toCurrency } = useCurrency();
  const { appConfig } = useAppConfig();

  const defaultPdfTemplate = useMemo(() => {
    const replacement = {
      campaign_name: donation.campaign.title,
      fund_name: donation.fund?.title ?? emptyCell(2),
      donor_name: sprintf('%s %s', donation.donor.first_name, donation.donor.last_name),
      donation_amount: toCurrency(donation.amount),
      payment_method: donation.payment_method.label,
      donation_date_time: format(donation.created_at, DATE_FORMATS.DATE_TIME),
    };

    template.content.greetings = shortcodeReplacement(
      template.short_codes?.filter((shortcode) => {
        if (shortcode.value === '{fund_name}') {
          return appConfig[AppConfigKeys.Campaign]?.allow_fund;
        }
        return true;
      }) ?? [],
      template.content.greetings,
      replacement,
    );

    return template;
  }, [template, donation, toCurrency, appConfig]);

  const windowHeight: number = window.innerHeight;

  return (
    <PDFViewer width="100%" height={windowHeight}>
      <PdfReceiptDocument
        pdfReceiptTemplate={defaultPdfTemplate}
        toCurrency={toCurrency}
        appConfig={appConfig}
      >
        <PdfDonationContent donation={donation} />
      </PdfReceiptDocument>
    </PDFViewer>
  );
};

export default DonationReceiptPdf;
