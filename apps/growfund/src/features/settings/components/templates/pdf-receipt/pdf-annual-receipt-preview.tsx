import { __ } from '@wordpress/i18n';

import { Box } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import { useAppConfig } from '@/contexts/app-config';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { isDefined } from '@/utils';

const DonationsTable = ({ pdfReceipt }: { pdfReceipt: PdfReceiptTemplate }) => {
  const primaryTextColor = pdfReceipt.colors?.primary_text ?? '#000000';
  const secondaryTextColor = pdfReceipt.colors?.secondary_text ?? '#636363';
  const { toCurrency } = useCurrency();
  return (
    <div className="growfund-w-full growfund-flex growfund-flex-col growfund-gap-4 growfund-py-6">
      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: secondaryTextColor }}
      >
        <div className="growfund-w-32">{__('Date', 'growfund')}</div>
        <div className="growfund-w-24">{__('Amount', 'growfund')}</div>
        <div className="growfund-w-28">{__('Donation ID', 'growfund')}</div>
        <div>{__('Payment Method', 'growfund')}</div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('Jan 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{564}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('Feb 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{563}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('Mar 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{562}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('Apr 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{561}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('May 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{560}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>

      <div
        className="growfund-w-full growfund-flex growfund-justify-stretch growfund-gap-2 growfund-typo-small"
        style={{ color: primaryTextColor }}
      >
        <div className="growfund-w-32">{__('Jun 5, 2025', 'growfund')}</div>
        <div className="growfund-w-24">{toCurrency(100)}</div>
        <div className="growfund-w-28">{559}</div>
        <div className="growfund-font-light" style={{ color: secondaryTextColor }}>
          {__('Bank Transfer', 'growfund')}
        </div>
      </div>
    </div>
  );
};

const PdfAnnualReceiptPreview = ({ pdfReceipt }: { pdfReceipt: PdfReceiptTemplate }) => {
  const { isDonationMode } = useAppConfig();
  const primaryTextColor = pdfReceipt.colors?.primary_text ?? '#000000';

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
            />
          </div>
        )}
        <div className="growfund-flex growfund-items-center growfund-justify-center">
          <h3 className="growfund-typo-h3" style={{ color: primaryTextColor }}>
            {__('ANNUAL RECEIPT', 'growfund')}
          </h3>
        </div>
      </div>
      <Separator />
      <div className="growfund-my-8 growfund-px-12 growfund-space-y-4">
        {isDefined(pdfReceipt.content) && (
          <div dangerouslySetInnerHTML={{ __html: pdfReceipt.content.greetings }} />
        )}

        <DonationsTable pdfReceipt={pdfReceipt} />

        <div className="growfund-typo-paragraph growfund-pt-6" style={{ color: primaryTextColor }}>
          {__('No goods or services were exchanged for these contributions.', 'growfund')}
        </div>

        {isDonationMode &&
          isDefined(pdfReceipt.content) &&
          isDefined(pdfReceipt.content.signature) &&
          pdfReceipt.content.signature.is_available && (
            <div className="growfund-space-y-2">
              <p className="growfund-typo-small" style={{ color: primaryTextColor }}>
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

export default PdfAnnualReceiptPreview;
