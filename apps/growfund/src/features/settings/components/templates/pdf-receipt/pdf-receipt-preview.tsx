import { __ } from '@wordpress/i18n';
import { format } from 'date-fns';

import { Box } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import { useAppConfig } from '@/contexts/app-config';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';

const PdfReceiptPreview = ({ pdfReceipt }: { pdfReceipt: PdfReceiptTemplate }) => {
  const { isDonationMode } = useAppConfig();
  const { toCurrency } = useCurrency();

  return (
    <Box
      className="growfund-overflow-hidden growfund-text-black"
      style={{ backgroundColor: pdfReceipt.colors?.background ?? '' }}
    >
      <div className="growfund-w-full growfund-my-8 growfund-p-2 lg:growfund-px-12 growfund-space-y-4">
        {isDefined(pdfReceipt.media) && pdfReceipt.media.image?.url && (
          <div
            className="growfund-w-full growfund-flex"
            style={{
              justifyContent: pdfReceipt.media.position,
              height: pdfReceipt.media.height ? `${pdfReceipt.media.height}px` : '',
            }}
          >
            <Image
              src={pdfReceipt.media.image.url}
              alt="Logo"
              rounded="none"
              style={{ height: pdfReceipt.media.height ? `${pdfReceipt.media.height}px` : '' }}
              className="growfund-border-none growfund-bg-transparent"
            />
          </div>
        )}
        <div className="growfund-flex growfund-items-center growfund-justify-center">
          <h3 className="growfund-typo-h3" style={{ color: pdfReceipt.colors?.primary_text ?? '' }}>
            {isDonationMode ? __('DONATION RECEIPT', 'growfund') : __('PLEDGE RECEIPT', 'growfund')}
          </h3>
        </div>
      </div>
      <Separator />
      <div className="growfund-my-8 growfund-px-12 growfund-space-y-4">
        {isDefined(pdfReceipt.content) && (
          <div dangerouslySetInnerHTML={{ __html: pdfReceipt.content.greetings }} />
        )}
        <div className="growfund-bg-secondary growfund-rounded-lg growfund-p-4 growfund-flex growfund-items-center growfund-justify-between">
          <div>
            <p
              className="growfund-typo-tiny"
              style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
            >
              {__('Donation Amount', 'growfund')}
            </p>
            <h3
              className="growfund-typo-h3"
              style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
            >
              {toCurrency(300)}
            </h3>
          </div>
          <div>
            <p
              className="growfund-typo-tiny"
              style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
            >
              {__('Date & Time', 'growfund')}
            </p>
            <p
              className="growfund-typo-small"
              style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
            >
              {format(new Date(), DATE_FORMATS.LOCALIZED_DATE_TIME)}
            </p>
          </div>
        </div>
        <div className="growfund-space-y-4">
          <p
            className="growfund-typo-small"
            style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
          >
            {__('Donation Details', 'growfund')}
          </p>
          <div className="growfund-space-y-1">
            <p
              className="growfund-typo-small"
              style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
            >
              {__('Donor Name', 'growfund')}
            </p>
            <p
              className="growfund-typo-small growfund-font-semibold"
              style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
            >
              Alex Jhonson
            </p>
          </div>
          <div className="growfund-space-y-1">
            <p
              className="growfund-typo-small"
              style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
            >
              {__('Donation To', 'growfund')}
            </p>
            <p
              className="growfund-typo-small growfund-font-semibold"
              style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
            >
              Wildfire Relief Fund 2024
            </p>
          </div>
          <div className="growfund-items-center growfund-grid growfund-grid-cols-2">
            <div className="growfund-space-y-1">
              <p
                className="growfund-typo-small"
                style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
              >
                {__('Transaction ID', 'growfund')}
              </p>
              <p
                className="growfund-typo-small growfund-font-semibold"
                style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
              >
                TXN-987654321
              </p>
            </div>
            <div className="growfund-space-y-1">
              <p
                className="growfund-typo-small"
                style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
              >
                {__('Payment Method', 'growfund')}
              </p>
              <p
                className="growfund-typo-small growfund-font-semibold"
                style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
              >
                Credit Card
              </p>
            </div>
          </div>
        </div>
        {isDefined(pdfReceipt.content) && pdfReceipt.content.tax_information?.is_available && (
          <div>
            <blockquote className="growfund-p-4 growfund-my-8 growfund-rounded-r-lg growfund-border-s-4 growfund-border-background-fill-brand growfund-bg-secondary">
              <p
                className="growfund-typo-tiny"
                style={{ color: pdfReceipt.colors?.secondary_text ?? '' }}
              >
                {__('Tax Information', 'growfund')}
              </p>
              <div
                className="growfund-mt-4"
                style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
                dangerouslySetInnerHTML={{
                  __html: pdfReceipt.content.tax_information.details ?? '',
                }}
              />
            </blockquote>
          </div>
        )}

        {isDonationMode &&
          isDefined(pdfReceipt.content) &&
          isDefined(pdfReceipt.content.signature) &&
          pdfReceipt.content.signature.is_available && (
            <div className="growfund-space-y-2">
              <p
                className="growfund-typo-small"
                style={{ color: pdfReceipt.colors?.primary_text ?? '' }}
              >
                {__('Sincerely', 'growfund')}
              </p>
              {pdfReceipt.content.signature.image?.url && (
                <div className="growfund-w-full growfund-flex growfund-justify-start growfund-my-4">
                  <Image
                    src={pdfReceipt.content.signature.image.url}
                    alt="signature"
                    rounded="none"
                    className="growfund-max-h-24 growfund-max-w-24 growfund-bg-transparent growfund-border-none"
                  />
                </div>
              )}
              {isDefined(pdfReceipt.content.signature.details) && (
                <div dangerouslySetInnerHTML={{ __html: pdfReceipt.content.signature.details }} />
              )}
            </div>
          )}
      </div>
      <Separator />
      <div className="growfund-my-4 growfund-p-2 lg:growfund-px-12">
        {isDefined(pdfReceipt.content) && isDefined(pdfReceipt.content.footer) && (
          <div dangerouslySetInnerHTML={{ __html: pdfReceipt.content.footer }} />
        )}
      </div>
    </Box>
  );
};

export default PdfReceiptPreview;
