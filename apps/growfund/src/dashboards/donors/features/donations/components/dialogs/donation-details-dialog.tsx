import { PDFDownloadLink } from '@react-pdf/renderer';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { DownloadIcon, HeartHandshake } from 'lucide-react';
import React, { useMemo } from 'react';

import { SpecialTributeIcon } from '@/app/icons';
import DonationStatusBadge from '@/components/donation-status-badge';
import PaymentStatusBadge from '@/components/payment-status-badge';
import PdfDonationContent from '@/components/pdf/contents/pdf-donation-content';
import PdfReceiptDocument from '@/components/pdf/pdf-receipt-document';
import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Image } from '@/components/ui/image';
import { OptionKeys } from '@/constants/option-keys';
import { useAppConfig } from '@/contexts/app-config';
import { type Donation } from '@/features/donations/schemas/donation';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { cn, shortcodeReplacement } from '@/lib/utils';
import { useGetOptionQuery } from '@/services/app-config';
import { emptyCell, isDefined } from '@/utils';

const DonationDetailsDialog = ({
  children,
  open,
  onOpenChange,
  donation,
}: React.PropsWithChildren<{
  open: boolean;
  onOpenChange: (open: boolean) => void;
  donation: Donation;
}>) => {
  const { toCurrency } = useCurrency();
  const { appConfig } = useAppConfig();

  const pdfReceiptTemplateQuery = useGetOptionQuery(OptionKeys.PDF_DONATION_RECEIPT_TEMPLATE);
  const defaultPdfTemplate = useMemo(() => {
    if (!isDefined(pdfReceiptTemplateQuery.data) || !isDefined(donation)) {
      return undefined;
    }

    const pdfReceiptTemplate = pdfReceiptTemplateQuery.data as PdfReceiptTemplate;

    const replacement = {
      campaign_name: donation.campaign.title,
      fund_name: donation.fund?.title ?? emptyCell(2),
      donor_name: sprintf('%s %s', donation.donor.first_name, donation.donor.last_name),
      donation_amount: toCurrency(donation.amount),
      payment_method: donation.payment_method.label,
      donation_date_time: format(donation.created_at, DATE_FORMATS.DATE_TIME),
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
  }, [pdfReceiptTemplateQuery.data, donation, toCurrency, appConfig]);

  const paymentData: { label: string; value: number; className?: string }[] = [
    {
      label: __('Donation Amount', 'growfund'),
      value: donation.amount,
    },
    {
      label: __('Total', 'growfund'),
      value: donation.amount,
      className: 'growfund-font-bold growfund-text-fg-primary growfund-pt-1',
    },
  ] as const;

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[33rem] growfund-max-h-[90svh] growfund-mb-4 growfund-flex growfund-flex-col">
        <DialogHeader>
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />
            {__('Donation Details', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        <div className="growfund-p-4 growfund-pt-0 growfund-space-y-2 growfund-overflow-y-auto growfund-flex-1 growfund-hide-scrollbar">
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <div className="growfund-typo-tiny growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-1">
              <span>{__('Created on', 'growfund')}</span>
              <span className="growfund-text-fg-muted-foreground">
                {format(donation.created_at, DATE_FORMATS.HUMAN_READABLE_DATE_WITH_TIME)}
              </span>
            </div>
            <DonationStatusBadge status={donation.status} />
          </div>
          <div className="growfund-space-y-4">
            {/* Campaign card */}
            <Box className="growfund-border-border-secondary growfund-shadow-none">
              <BoxContent className="growfund-p-2 growfund-grid growfund-grid-cols-[3.5rem_auto] growfund-gap-3">
                <Image
                  src={donation.campaign.images?.[0]?.url ?? null}
                  alt={donation.campaign.title}
                  className="growfund-w-full"
                  fit="cover"
                  aspectRatio="square"
                />
                <div className="growfund-space-y-2">
                  <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                    {donation.campaign.title}
                  </p>
                  <div className="growfund-typo-tiny growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-1">
                    {__('by', 'growfund')}
                    <span className="growfund-text-fg-brand growfund-capitalize">
                      {donation.campaign.author?.display_name ?? emptyCell(2)}
                    </span>
                  </div>
                </div>
              </BoxContent>
            </Box>

            {/* Donation card */}
            <Box className="growfund-border-border-secondary growfund-shadow-none">
              <BoxContent className="growfund-p-4 growfund-flex growfund-items-center growfund-justify-between growfund-gap-3">
                <div className="growfund-space-y-2">
                  <p className="growfund-typo-small growfund-text-fg-secondary">
                    {__('Donation Amount', 'growfund')}
                  </p>
                  <div className="growfund-typo-h3 growfund-text-fg-primary">{toCurrency(donation.amount)}</div>
                  {isDefined(donation.tribute_type) && (
                    <div className="growfund-w-full growfund-flex growfund-items-center growfund-gap-1">
                      <SpecialTributeIcon className="growfund-size-4" />
                      <span className="growfund-typo-tiny growfund-text-fg-special">
                        {sprintf(
                          /* translators: 1: Tribute type 2: Tribute salutation 3: Tribute to */
                          __('Tribute %1$s %2$s %3$s', 'growfund'),
                          donation.tribute_type,
                          donation.tribute_salutation,
                          donation.tribute_to,
                        )}
                      </span>
                    </div>
                  )}
                </div>
                {isDefined(donation.tribute_notification_type) && (
                  <div className="growfund-space-y-2">
                    <p className="growfund-typo-small growfund-text-fg-secondary">
                      {__('Tribute Card Recipient', 'growfund')}
                    </p>
                    <div className="growfund-type-h6">{donation.tribute_notification_recipient_name}</div>
                    <div className="growfund-flex growfund-items-center growfund-gap-1">
                      <div>{__('via', 'growfund')}</div>
                      {(donation.tribute_notification_type === 'send-ecard' ||
                        donation.tribute_notification_type === 'send-ecard-and-post-mail') && (
                        <Badge variant={'secondary'} className="growfund-bg-background-fill-secondary">
                          {__('e-card', 'growfund')}
                        </Badge>
                      )}

                      {(donation.tribute_notification_type === 'send-post-mail' ||
                        donation.tribute_notification_type === 'send-ecard-and-post-mail') && (
                        <Badge variant={'secondary'} className="growfund-bg-background-fill-secondary">
                          {__('post mail', 'growfund')}
                        </Badge>
                      )}
                    </div>
                  </div>
                )}
              </BoxContent>
            </Box>

            <Box className="growfund-border-border-secondary growfund-shadow-none growfund-mb-10">
              <BoxContent className="growfund-space-y-3">
                <div className="growfund-flex growfund-items-center growfund-justify-between">
                  <h6 className="growfund-typo-h6 growfund-text-fg-primary">{__('Payment', 'growfund')}</h6>
                  <PaymentStatusBadge status={donation.payment_status ?? 'unpaid'} />
                </div>

                <Box>
                  <BoxContent className="growfund-space-y-2 growfund-p-3">
                    {paymentData.map((data, index) => {
                      return (
                        <div
                          key={index}
                          className={cn(
                            'growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-font-medium growfund-text-fg-secondary',
                            data.className,
                          )}
                        >
                          <p>{data.label}</p>
                          <p>{toCurrency(data.value)}</p>
                        </div>
                      );
                    })}
                  </BoxContent>
                </Box>
              </BoxContent>
            </Box>
          </div>
        </div>
        {donation.status === 'completed' && (
          <DialogFooter className="growfund-bg-background-surface-secondary">
            <PDFDownloadLink
              document={
                <PdfReceiptDocument
                  pdfReceiptTemplate={defaultPdfTemplate}
                  toCurrency={toCurrency}
                  appConfig={appConfig}
                >
                  <PdfDonationContent donation={donation} />
                </PdfReceiptDocument>
              }
              fileName={sprintf(
                /* translators: 1: Donation ID 2: current time */
                'donation-receipt-%1$s-%2$s.pdf',
                donation.id,
                format(new Date(), DATE_FORMATS.TIME),
              )}
            >
              <Button
                variant="ghost"
                className="growfund-bg-background-fill growfund-border-border growfund-py-2 growfund-px-4 growfund-h-9"
              >
                <DownloadIcon />
                {__('Download PDF Receipt', 'growfund')}
              </Button>
            </PDFDownloadLink>
          </DialogFooter>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default DonationDetailsDialog;
