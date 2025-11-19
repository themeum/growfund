import { __ } from '@wordpress/i18n';
import { FolderInput } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { useAppConfig } from '@/contexts/app-config';
import ReassignFundDialog from '@/features/donations/components/dialogs/reassign-fund-dialog';
import { type Donation } from '@/features/donations/schemas/donation';
import { useDonationBulkActionsMutation } from '@/features/donations/services/donations';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { isDefined } from '@/utils';

interface DonationPaymentViewCardProps {
  donation: Donation;
}

const DonationPaymentViewCard = ({ donation }: DonationPaymentViewCardProps) => {
  const { appConfig } = useAppConfig();
  const [isReassignFundDialogOpen, setIsReassignFundDialogOpen] = useState(false);

  const donationBulkActionsMutation = useDonationBulkActionsMutation();

  return (
    <Box>
      <BoxContent className="growfund-space-y-5">
        <h6 className="growfund-text-fg-primary growfund-font-semibold growfund-typo-h6">
          {__('Donation', 'growfund')}
        </h6>
        {isDefined(donation.fund) && appConfig[AppConfigKeys.Campaign]?.allow_fund && (
          <div className="growfund-flex growfund-items-center growfund-justify-between">
            <div className="growfund-space-y-1">
              <div className="growfund-typo-small growfund-text-fg-secondary">{__('Fund', 'growfund')}</div>
              <div className="growfund-typo-medium growfund-text-fg-emphasis">
                {__(donation.fund.title, 'growfund')}
              </div>
            </div>
            <ReassignFundDialog
              onReassign={(closeDialog, fundId) => {
                donationBulkActionsMutation.mutate(
                  {
                    ids: [donation.id],
                    action: 'reassign_fund',
                    fund_id: fundId,
                  },
                  {
                    onSuccess() {
                      closeDialog();
                      toast.success(
                        __('Reassigned the donations to the selected fund.', 'growfund'),
                      );
                    },
                  },
                );
              }}
              open={isReassignFundDialogOpen}
              onOpenChange={setIsReassignFundDialogOpen}
              data={[{ id: donation.fund.id }]}
              loading={donationBulkActionsMutation.isPending}
            >
              <Button variant={'secondary'}>
                <FolderInput /> {__('Reassign Fund', 'growfund')}
              </Button>
            </ReassignFundDialog>
          </div>
        )}
      </BoxContent>
    </Box>
  );
};

export default DonationPaymentViewCard;
