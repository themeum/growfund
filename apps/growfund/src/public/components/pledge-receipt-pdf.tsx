import { PDFViewer } from '@react-pdf/renderer';
import { sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { useMemo } from 'react';

import PDFPledgeWithRewardContent from '@/components/pdf/contents/pdf-pledge-with-reward-content';
import PdfPledgeWithoutRewardContent from '@/components/pdf/contents/pdf-pledge-without-reward-content';
import PdfReceiptDocument from '@/components/pdf/pdf-receipt-document';
import { useAppConfig } from '@/contexts/app-config';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { shortcodeReplacement } from '@/lib/utils';
import { emptyCell, isDefined } from '@/utils';

const PledgeReceiptPdf = ({
  pledge,
  template,
}: {
  pledge: Pledge;
  template: PdfReceiptTemplate;
}) => {
  const { toCurrency } = useCurrency();
  const { appConfig } = useAppConfig();

  const defaultPdfTemplate = useMemo(() => {
    const replacement = {
      campaign_name: pledge.campaign.title,
      backer_name: sprintf('%s %s', pledge.backer.first_name, pledge.backer.last_name),
      pledge_amount: isDefined(pledge.payment.total)
        ? toCurrency(pledge.payment.total)
        : emptyCell(2),
      payment_method: pledge.payment.payment_method.label,
      pledge_date_time: isDefined(pledge.created_at)
        ? format(pledge.created_at, DATE_FORMATS.HUMAN_READABLE_FULL_DATE_TIME)
        : emptyCell(2),
    };

    template.content.greetings = shortcodeReplacement(
      template.short_codes ?? [],
      template.content.greetings,
      replacement,
    );

    return template;
  }, [template, pledge, toCurrency]);

  const windowHeight: number = window.innerHeight;

  return (
    <PDFViewer width="100%" height={windowHeight}>
      <PdfReceiptDocument
        pdfReceiptTemplate={defaultPdfTemplate}
        toCurrency={toCurrency}
        appConfig={appConfig}
      >
        {isDefined(pledge.reward) ? (
          <PDFPledgeWithRewardContent pledge={pledge} />
        ) : (
          <PdfPledgeWithoutRewardContent pledge={pledge} />
        )}
      </PdfReceiptDocument>
    </PDFViewer>
  );
};

export default PledgeReceiptPdf;
