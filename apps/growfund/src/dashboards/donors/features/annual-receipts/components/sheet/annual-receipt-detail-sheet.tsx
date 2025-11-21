import { PDFViewer } from '@react-pdf/renderer';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { FileSpreadsheet } from 'lucide-react';
import React, { useMemo, useState } from 'react';

import { ErrorIcon } from '@/app/icons';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { Container } from '@/components/layouts/container';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import PdfAnnualReceiptContent from '@/components/pdf/contents/pdf-annual-receipt-content';
import PdfAnnualReceiptDocument from '@/components/pdf/pdf-annual-receipt-document';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { growfundConfig } from '@/config/growfund';
import { OptionKeys } from '@/constants/option-keys';
import { useAppConfig } from '@/contexts/app-config';
import { useAnnualReceiptDonationsByYear } from '@/dashboards/donors/features/annual-receipts/services/donor-annual-receipt';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { shortcodeReplacement } from '@/lib/utils';
import { useGetOptionQuery } from '@/services/app-config';
import { isDefined } from '@/utils';

const AnnualReceiptDetailSheet = ({
  year,
  children,
}: React.PropsWithChildren<{ year: string }>) => {
  const [open, setOpen] = useState(false);
  const { toCurrency } = useCurrency();
  const { appConfig } = useAppConfig();
  const pdfReceiptTemplateQuery = useGetOptionQuery(OptionKeys.PDF_ANNUAL_RECEIPT_TEMPLATE, open);
  const annualReceiptQuery = useAnnualReceiptDonationsByYear(year, open);

  const annualReceiptDetail = useMemo(() => {
    if (!isDefined(annualReceiptQuery.data)) {
      return undefined;
    }

    return annualReceiptQuery.data;
  }, [annualReceiptQuery.data]);

  const pdfReceiptTemplate = useMemo(() => {
    if (!isDefined(pdfReceiptTemplateQuery.data) || !isDefined(annualReceiptDetail)) {
      return undefined;
    }

    const pdfReceiptTemplate = pdfReceiptTemplateQuery.data as PdfReceiptTemplate;

    const replacement = {
      donor_name: sprintf(
        '%s %s',
        annualReceiptDetail.donor.first_name,
        annualReceiptDetail.donor.last_name,
      ),
      total_donation_amount: toCurrency(
        annualReceiptDetail.donations.map((donation) => donation.amount).reduce((a, b) => a + b, 0),
      ),
      date: format(new Date(), DATE_FORMATS.HUMAN_READABLE),
      site_url: growfundConfig.site_url,
      organization_name: appConfig[AppConfigKeys.General]?.organization.name ?? '',
    };

    pdfReceiptTemplate.content.greetings = shortcodeReplacement(
      pdfReceiptTemplate.short_codes?.filter((shortcode) => {
        if (shortcode.value === '{fund_name}') {
          return appConfig[AppConfigKeys.Campaign]?.allow_fund;
        }
        return true;
      }) ?? [],
      pdfReceiptTemplate.content.greetings,
      replacement,
    );

    return pdfReceiptTemplate;
  }, [pdfReceiptTemplateQuery.data, annualReceiptDetail, toCurrency, appConfig]);

  return (
    <Sheet
      onOpenChange={(open) => {
        setOpen(open);
      }}
    >
      <SheetTrigger asChild>{children}</SheetTrigger>
      <SheetContent side="bottom">
        <SheetHeader>
          <SheetTitle>
            <FileSpreadsheet className="growfund-size-6" />
            {__('Annual Receipt', 'growfund')}
          </SheetTitle>
        </SheetHeader>

        <Container className="growfund-mt-7 growfund-flex growfund-justify-center growfund-min-h-[80svh]">
          {(annualReceiptQuery.isLoading ||
            annualReceiptQuery.isPending ||
            pdfReceiptTemplateQuery.isLoading ||
            pdfReceiptTemplateQuery.isPending) && <LoadingSpinnerOverlay />}

          {!isDefined(annualReceiptDetail) ? (
            <ErrorState>
              <ErrorIcon />
              <ErrorStateDescription>
                {__('Error loading annual receipt', 'growfund')}
              </ErrorStateDescription>
            </ErrorState>
          ) : (
            <PDFViewer width="100%" height={720}>
              <PdfAnnualReceiptDocument
                pdfReceiptTemplate={pdfReceiptTemplate}
                toCurrency={toCurrency}
                appConfig={appConfig}
              >
                <PdfAnnualReceiptContent annualReceipt={annualReceiptDetail} />
              </PdfAnnualReceiptDocument>
            </PDFViewer>
          )}
        </Container>
      </SheetContent>
    </Sheet>
  );
};

export default AnnualReceiptDetailSheet;
