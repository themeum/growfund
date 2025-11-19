import { DotFilledIcon, DotsVerticalIcon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Clock4Icon, FileText, X } from 'lucide-react';
import { useState } from 'react';

import { SpecialTributeIcon } from '@/app/icons';
import { Badge, type BadgeProps } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Image } from '@/components/ui/image';
import DonationDetailsDialog from '@/dashboards/donors/features/donations/components/dialogs/donation-details-dialog';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { type Donation, type DonationStatus } from '@/features/donations/schemas/donation';
import { useUpdateDonationStatusMutation } from '@/features/donations/services/donations';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const DonationCard = ({ donation }: { donation: Donation }) => {
  const [openDetailsDialog, setOpenDetailsDialog] = useState(false);
  const { toCurrency } = useCurrency();
  const { openDialog } = useConsentDialog();
  const variants = new Map<DonationStatus, BadgeProps['variant']>([
    ['completed', 'primary'],
    ['pending', 'secondary'],
    ['cancelled', 'destructive'],
    ['failed', 'destructive'],
    ['refunded', 'special'],
  ]);
  const statusTexts = new Map<DonationStatus, string>([
    ['completed', __('Completed', 'growfund')],
    ['pending', __('Pending', 'growfund')],
    ['failed', __('Failed', 'growfund')],
    ['cancelled', __('Cancelled', 'growfund')],
    ['refunded', __('Refunded', 'growfund')],
  ]);

  const updateDonationStatusMutation = useUpdateDonationStatusMutation();

  const isCancellable = ['pending'].includes(donation.status);

  return (
    <Card>
      <CardContent className="growfund-pt-4">
        <div className="growfund-grid growfund-grid-cols-[35rem_auto] growfund-w-full growfund-gap-3">
          <div className="growfund-w-full growfund-flex growfund-items-center growfund-gap-3">
            <Image
              src={donation.campaign.images?.[0]?.url ?? null}
              alt={'image'}
              fit="cover"
              aspectRatio="square"
              className="growfund-h-24 growfund-w-24 growfund-rounded"
            />
            <div className="growfund-space-y-1">
              {isDefined(donation.tribute_type) && (
                <div className="growfund-w-full growfund-flex growfund-items-center growfund-gap-1">
                  <SpecialTributeIcon className="growfund-size-4 growfund-text-fg-special" />
                  <span className="growfund-typo-tiny growfund-text-fg-special">
                    {sprintf(
                      /* translators: 1: Tribute Type, 2: Tribute Salutation, 3: Tribute To */
                      __('Tribute %1$s %2$s %3$s', 'growfund'),
                      donation.tribute_type,
                      donation.tribute_salutation,
                      donation.tribute_to,
                    )}
                  </span>
                </div>
              )}

              <div className="growfund-space-y-2">
                <div className="growfund-flex growfund-w-full growfund-items-center growfund-gap-2">
                  <div className="growfund-typo-h4 growfund-font-semibold growfund-text-primary">
                    {toCurrency(donation.amount)}
                  </div>
                  <Badge
                    variant={
                      variants.has(donation.status) ? variants.get(donation.status) : 'outline'
                    }
                    className="growfund-capitalize"
                  >
                    {statusTexts.has(donation.status)
                      ? statusTexts.get(donation.status)
                      : donation.status}
                  </Badge>
                </div>
                <div className="growfund-w-full growfund-flex growfund-items-center">
                  <div className="growfund-typo-small growfund-font-medium growfund-text-primary">
                    {donation.campaign.title}
                  </div>
                  <DotFilledIcon className="growfund-w-4 growfund-h-4" />
                  <div className="growfund-typo-small growfund-font-medium growfund-text-fg-emphasis">
                    {/* translators: %s: Fund title */}
                    {sprintf(__('Fund: %s', 'growfund'), donation.fund?.title)}
                  </div>
                </div>
                <div className="growfund-flex growfund-gap-1 growfund-items-center">
                  <Clock4Icon className="growfund-text-icon-primary growfund-h-4 growfund-w-4" />
                  <span className="growfund-typo-small growfund-text-muted-foreground">
                    {format(
                      new Date(donation.created_at),
                      DATE_FORMATS.HUMAN_READABLE_FULL_DATE_TIME,
                    )}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div className="growfund-flex growfund-flex-col growfund-justify-between growfund-items-end">
            <DropdownMenu>
              <DropdownMenuTrigger asChild>
                <Button variant="secondary" size="icon" className="growfund-shrink-0 growfund-size-8">
                  <DotsVerticalIcon />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent align="start">
                <DropdownMenuItem
                  onClick={() => {
                    setOpenDetailsDialog(true);
                  }}
                >
                  <FileText />
                  {__('View Details', 'growfund')}
                </DropdownMenuItem>
                <DropdownMenuItem
                  className={cn('growfund-text-fg-critical', !isCancellable && 'growfund-text-fg-subdued')}
                  disabled={!isCancellable}
                  onClick={() => {
                    openDialog({
                      title: __('Cancel Donation', 'growfund'),
                      content: __(
                        'Are you sure you want to cancel this donation? This action cannot be undone.',
                        'growfund',
                      ),
                      confirmButtonVariant: 'destructive',
                      confirmText: __('Yes, cancel the donation', 'growfund'),
                      declineText: __('Keep as it is', 'growfund'),
                      onConfirm: async (closeDialog) => {
                        await updateDonationStatusMutation.mutateAsync({
                          id: donation.id,
                          status: 'cancelled',
                        });
                        closeDialog();
                      },
                    });
                  }}
                >
                  <X />
                  {__('Cancel Donation', 'growfund')}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <span className="growfund-typo-tiny growfund-text-fg-muted">
              {/* translators: %s: Donation ID */}
              {sprintf(__('ID: %s', 'growfund'), donation.id)}
            </span>
          </div>
        </div>
        <DonationDetailsDialog
          open={openDetailsDialog}
          onOpenChange={setOpenDetailsDialog}
          donation={donation}
        />
      </CardContent>
    </Card>
  );
};

export default DonationCard;
