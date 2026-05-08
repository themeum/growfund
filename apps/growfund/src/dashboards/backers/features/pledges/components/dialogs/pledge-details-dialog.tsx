import { ArrowTopRightIcon } from '@radix-ui/react-icons';
import { PDFDownloadLink } from '@react-pdf/renderer';
import { __, _n, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { DownloadIcon, HeartHandshake } from 'lucide-react';
import React, { useMemo, useState } from 'react';

import { GiftPackIcon } from '@/app/icons';
import PaymentStatusBadge from '@/components/payment-status-badge';
import PDFPledgeWithRewardContent from '@/components/pdf/contents/pdf-pledge-with-reward-content';
import PdfPledgeWithoutRewardContent from '@/components/pdf/contents/pdf-pledge-without-reward-content';
import PdfReceiptDocument from '@/components/pdf/pdf-receipt-document';
import PledgeStatusBadge from '@/components/pledge-status-badge';
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
import { type RewardItem } from '@/features/campaigns/schemas/reward-item';
import { useRewardItemDownloadMutation } from '@/features/campaigns/services/reward-item';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { cn, shortcodeReplacement } from '@/lib/utils';
import { useGetOptionQuery } from '@/services/app-config';
import { emptyCell, isDefined } from '@/utils';

const MAX_ITEMS_TO_SHOW = 2;

const PledgeDetailsDialog = ({
  children,
  open,
  onOpenChange,
  pledge,
}: React.PropsWithChildren<{
  open: boolean;
  onOpenChange: (open: boolean) => void;
  pledge: Pledge;
}>) => {
  const { appConfig } = useAppConfig();
  const [showMore, setShowMore] = useState(false);
  const { toCurrency } = useCurrency();

  const pdfReceiptTemplateQuery = useGetOptionQuery(OptionKeys.PDF_PLEDGE_RECEIPT_TEMPLATE);
  const defaultPdfTemplate = useMemo(() => {
    if (!isDefined(pdfReceiptTemplateQuery.data) || !isDefined(pledge)) {
      return undefined;
    }

    const pdfReceiptTemplate = pdfReceiptTemplateQuery.data as PdfReceiptTemplate;

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

    pdfReceiptTemplate.content.greetings = shortcodeReplacement(
      pdfReceiptTemplate.short_codes ?? [],
      pdfReceiptTemplate.content.greetings,
      replacement,
    );

    return pdfReceiptTemplate;
  }, [pdfReceiptTemplateQuery.data, pledge, toCurrency]);

  const paymentData: { label: string; value: number; className?: string; hidden?: boolean }[] = [
    {
      label: __('Pledge Amount', 'growfund'),
      value: pledge.payment.amount ?? 0,
    },
    {
      label: __('Shipping', 'growfund'),
      value: pledge.payment.shipping_cost ?? 0,
      hidden:
        pledge.pledge_option === 'with-rewards' && pledge.reward?.reward_type === 'digital-goods',
    },
    {
      label: __('Bonus', 'growfund'),
      value: pledge.payment.bonus_support_amount ?? 0,
      hidden: !!pledge.payment.bonus_support_amount && pledge.pledge_option !== 'without-rewards',
    },
    {
      label: __('Recovery Fee', 'growfund'),
      value: pledge.payment.recovery_fee ?? 0,
      hidden: true,
    },
    {
      label: __('Total', 'growfund'),
      value: pledge.payment.total ?? 0,
      className: 'growfund-font-bold growfund-text-fg-primary growfund-pt-1',
    },
  ] as const;

  const rewardItemDownloadMutation = useRewardItemDownloadMutation();

  const handleDownload = (item: RewardItem) => {
    rewardItemDownloadMutation.mutate(
      { uid: pledge.uid, rewardItemId: item.id },
      {
        onSuccess: (downloadLink) => {
          if (downloadLink) {
            window.open(downloadLink, '_blank', 'noopener,noreferrer');
          }
        },
      },
    );
  };

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[33rem] growfund-max-h-[90svh] growfund-mb-4 growfund-flex growfund-flex-col">
        <DialogHeader>
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <HeartHandshake className="growfund-size-6 growfund-text-icon-primary" />
            {__('Pledge Details', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        <div className="growfund-p-4 growfund-pt-0 growfund-space-y-2 growfund-overflow-y-auto growfund-flex-1 growfund-hide-scrollbar">
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <div className="growfund-typo-tiny growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-1">
              <span>{__('Pledged on', 'growfund')}</span>
              <span className="growfund-text-fg-muted-foreground">
                {format(pledge.created_at, DATE_FORMATS.HUMAN_READABLE_DATE_WITH_TIME)}
              </span>
            </div>
            <PledgeStatusBadge status={pledge.status} />
          </div>
          <div className="growfund-space-y-4">
            {/* Campaign card */}
            <Box className="growfund-border-border-secondary growfund-shadow-none">
              <BoxContent className="growfund-p-2 growfund-grid growfund-grid-cols-[3.5rem_auto] growfund-gap-3">
                <Image
                  src={pledge.campaign.images?.[0]?.url ?? null}
                  alt={pledge.campaign.title}
                  className="growfund-w-full"
                  fit="cover"
                  aspectRatio="square"
                />
                <div className="growfund-space-y-2">
                  <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                    {pledge.campaign.title}
                  </p>
                  <div className="growfund-typo-tiny growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-1">
                    {__('by', 'growfund')}
                    <span className="growfund-text-fg-brand growfund-capitalize">
                      {pledge.campaign.author?.display_name ?? emptyCell(2)}
                    </span>
                  </div>
                </div>
              </BoxContent>
            </Box>

            {/* Reward card */}
            {isDefined(pledge.reward) && (
              <Box className="growfund-border-border-secondary growfund-shadow-none">
                <BoxContent className="growfund-space-y-3">
                  <div className="growfund-flex growfund-items-center growfund-justify-center">
                    <GiftPackIcon className="growfund-flex growfund-items-center" />
                    <h6 className="growfund-typo-h6 growfund-text-fg-primary growfund-flex growfund-items-center">
                      {__('Rewards', 'growfund')}
                    </h6>
                  </div>

                  <Box className="growfund-border-none growfund-shadow-none growfund-bg-background-surface-secondary">
                    <BoxContent className="growfund-p-3 growfund-grid growfund-grid-cols-[5.5rem_auto] growfund-gap-3">
                      <Image
                        src={pledge.reward.image?.url ?? null}
                        alt={pledge.reward.title}
                        className="growfund-w-full"
                        fit="cover"
                        aspectRatio="square"
                      />
                      <div className="growfund-space-y-1 growfund-flex growfund-flex-col growfund-gap-3 growfund-mt-1">
                        <p
                          className="growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-truncate-2-lines"
                          title={pledge.reward.title}
                        >
                          {pledge.reward.title}
                        </p>
                        <h6 className="growfund-typo-h6 growfund-text-fg-secondary">
                          {toCurrency(pledge.reward.amount)}
                        </h6>
                      </div>
                    </BoxContent>
                  </Box>

                  <div className="growfund-space-y-2">
                    <p className="growfund-typo-small growfund-font-medium growfund-text-fg-secondary">
                      {sprintf(
                        /* translators: %s: number of reward items */
                        _n(
                          '%s item included',
                          '%s items included',
                          pledge.reward.items.length,
                          'growfund',
                        ),
                        pledge.reward.items.length,
                      )}
                    </p>

                    <div className="growfund-space-y-2">
                      {pledge.reward.items
                        .slice(0, showMore ? pledge.reward.items.length : MAX_ITEMS_TO_SHOW)
                        .map((item, index) => {
                          return (
                            <Box key={index} className="growfund-shadow-none">
                              <BoxContent className="growfund-p-3 growfund-flex growfund-justify-between growfund-items-center growfund-gap-3">
                                <div className="growfund-flex growfund-gap-3">
                                  <Image
                                    src={item.image?.url ?? null}
                                    alt={item.title}
                                    className="growfund-w-16 growfund-h-16 growfund-object-cover"
                                    fit="cover"
                                    aspectRatio="square"
                                  />
                                  <div className="growfund-space-y-1">
                                    <p className="growfund-typo-small growfund-text-fg-primary">
                                      {item.title}
                                    </p>
                                    <p className="growfund-typo-tiny growfund-text-fg-secondary">
                                      {/* translators: %s: reward quantity */}
                                      {sprintf(__('Quantity: %s', 'growfund'), item.quantity)}
                                    </p>
                                  </div>
                                </div>
                                <div>
                                  {item.can_download && (
                                    <Button
                                      variant="link"
                                      size="sm"
                                      className="growfund-flex growfund-items-center growfund-gap-2 growfund-text-fg-emphasis"
                                      onClick={() => {
                                        handleDownload(item);
                                      }}
                                    >
                                      {item.asset_type === 'url' ? (
                                        <ArrowTopRightIcon />
                                      ) : (
                                        <DownloadIcon />
                                      )}
                                      {item.asset_type === 'url'
                                        ? __('Visit', 'growfund')
                                        : __('Download', 'growfund')}
                                    </Button>
                                  )}
                                </div>
                              </BoxContent>
                            </Box>
                          );
                        })}

                      {pledge.reward.items.length > MAX_ITEMS_TO_SHOW && (
                        <Button
                          variant="link"
                          size="sm"
                          className="hover:growfund-bg-transparent growfund-px-0 growfund-text-fg-subdued hover:growfund-text-fg-secondary"
                          onClick={() => {
                            setShowMore(!showMore);
                          }}
                        >
                          {!showMore
                            ? sprintf(
                                /* translators: %s: number of reward items */
                                _n(
                                  '+%s more item',
                                  '+%s more items',
                                  pledge.reward.items.length - MAX_ITEMS_TO_SHOW,
                                  'growfund',
                                ),
                                pledge.reward.items.length - MAX_ITEMS_TO_SHOW,
                              )
                            : __('Show less', 'growfund')}
                        </Button>
                      )}
                    </div>
                  </div>
                </BoxContent>
              </Box>
            )}
            <Box>
              <BoxContent>
                <h6 className="growfund-typo-h6 growfund-text-fg-primary">
                  {__('Local Pickup', 'growfund')}
                </h6>
                <div
                  className="growfund-mt-3 growfund-bg-background-surface-secondary growfund-rounded-sm growfund-p-4 growfund-typo-small growfund-text-fg-secondary growfund-rich-text-content"
                  dangerouslySetInnerHTML={{
                    __html: pledge.reward?.local_pickup_instructions ?? '',
                  }}
                />
              </BoxContent>
            </Box>

            <Box className="growfund-border-border-secondary growfund-shadow-none growfund-mb-10">
              <BoxContent className="growfund-space-y-3">
                <div className="growfund-flex growfund-items-center growfund-justify-between">
                  <h6 className="growfund-typo-h6 growfund-text-fg-primary">
                    {__('Payment', 'growfund')}
                  </h6>
                  <PaymentStatusBadge status={pledge.payment.payment_status ?? 'pending'} />
                </div>

                <Box>
                  <BoxContent className="growfund-space-y-2 growfund-p-3">
                    {paymentData
                      .filter((data) => !isDefined(data.hidden) || !data.hidden)
                      .map((data, index) => {
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
        {(pledge.status === 'backed' || pledge.status === 'completed') && (
          <DialogFooter className="growfund-bg-background-surface-secondary">
            <PDFDownloadLink
              document={
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
              }
              fileName={sprintf(
                /* translators: 1: pledge id, 2: current date */
                'pledge-receipt-%1$s-%2$s.pdf',
                pledge.id,
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

export default PledgeDetailsDialog;
