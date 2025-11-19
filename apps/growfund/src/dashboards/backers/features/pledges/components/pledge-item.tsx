import { DotsVerticalIcon } from '@radix-ui/react-icons';
import { __, _n, sprintf } from '@wordpress/i18n';
import { format } from 'date-fns';
import { Clock, FileText, Gift, X } from 'lucide-react';
import { useState } from 'react';

import PledgeStatusBadge from '@/components/pledge-status-badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { DotSeparator } from '@/components/ui/dot-separator';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import PledgeDetailsDialog from '@/dashboards/backers/features/pledges/components/dialogs/pledge-details-dialog';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import { useUpdatePledgeStatusMutation } from '@/features/pledges/services/pledges';
import { useCurrency } from '@/hooks/use-currency';
import { DATE_FORMATS } from '@/lib/date';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface PledgeItemProps {
  pledge: Pledge;
}

const PledgeItem = ({ pledge }: PledgeItemProps) => {
  const { toCurrency } = useCurrency();
  const [openDetailsDialog, setOpenDetailsDialog] = useState(false);

  const updatePledgeStatusMutation = useUpdatePledgeStatusMutation();

  const { openDialog } = useConsentDialog();

  const isCancellable = ['pending'].includes(pledge.status);

  return (
    <Box className="growfund-border-none growfund-shadow-none">
      <BoxContent>
        <div className="growfund-grid growfund-grid-cols-[5.5rem_auto_12rem] growfund-gap-3">
          <Image
            src={pledge.campaign.images?.[0]?.url ?? null}
            alt={pledge.campaign.title}
            className="growfund-w-[5.5rem]"
            fit="cover"
            aspectRatio="square"
          />
          <div className="growfund-space-y-2">
            <div className="growfund-space-y-1">
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <h4 className="growfund-typo-h4 growfund-text-fg-primary">
                  {toCurrency(pledge.payment.amount ?? 0)}
                </h4>
                <PledgeStatusBadge status={pledge.status} />
              </div>
              <p
                title={pledge.campaign.title}
                className="growfund-typo-paragraph growfund-font-medium growfund-text-fg-primary growfund-truncate-2-lines"
              >
                {pledge.campaign.title}
              </p>
            </div>
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-shrink-0">
                <Clock className="growfund-size-4 growfund-text-icon-primary" />
                <span className="growfund-typo-small growfund-text-fg-muted">
                  {format(pledge.created_at, DATE_FORMATS.HUMAN_READABLE_DATE_WITH_TIME)}
                </span>
              </div>

              {isDefined(pledge.reward) && (
                <>
                  <Separator orientation="vertical" className="growfund-h-4 growfund-bg-fg-muted" />
                  <div className="growfund-flex growfund-items-center growfund-gap-1">
                    <Gift className="growfund-size-4 growfund-text-icon-emphasis" />
                    <span
                      title={pledge.reward.title}
                      className="growfund-typo-small growfund-text-fg-emphasis growfund-truncate growfund-max-w-48"
                    >
                      {pledge.reward.title}
                    </span>
                    <DotSeparator className="growfund-bg-fg-emphasis growfund-mx-1" />
                    <span className="growfund-typo-small growfund-text-fg-emphasis growfund-shrink-0">
                      {sprintf(
                        /* translators: %d: number of reward items */
                        _n('%d item', '%d items', pledge.reward.items.length, 'growfund'),
                        pledge.reward.items.length,
                      )}
                    </span>
                  </div>
                </>
              )}
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
                      title: __('Cancel Pledge', 'growfund'),
                      content: __(
                        'Are you sure you want to cancel this pledge? This action cannot be undone.',
                        'growfund',
                      ),
                      confirmButtonVariant: 'destructive',
                      confirmText: __('Yes, cancel the pledge', 'growfund'),
                      declineText: __('Keep as it is', 'growfund'),
                      onConfirm: async (closeDialog) => {
                        await updatePledgeStatusMutation.mutateAsync({
                          id: pledge.id,
                          status: 'cancelled',
                        });
                        closeDialog();
                      },
                    });
                  }}
                >
                  <X />
                  {__('Cancel Pledge', 'growfund')}
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

            <span className="growfund-typo-tiny growfund-text-fg-muted">
              {/* translators: %s: pledge ID */}
              {sprintf(__('ID: %s', 'growfund'), pledge.id)}
            </span>
          </div>
        </div>
        <PledgeDetailsDialog
          open={openDetailsDialog}
          onOpenChange={setOpenDetailsDialog}
          pledge={pledge}
        />
      </BoxContent>
    </Box>
  );
};

export default PledgeItem;
