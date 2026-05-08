import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { useNavigate } from 'react-router';

import { Alert } from '@/components/ui/alert';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { FormLabel } from '@/components/ui/form';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectSeparator,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { RouteConfig } from '@/config/route-config';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import { DonationActionBadge } from '@/features/donations/components/donation-action/donation-action-badge';
import {
  getActionAlerts,
  getActionHeaderText,
  prepareActionOptions,
  prepareMessageKey,
} from '@/features/donations/components/donation-action/donation-action-services';
import { type Donation } from '@/features/donations/schemas/donation';
import {
  useDeleteDonationMutation,
  useUpdateDonationStatusMutation,
} from '@/features/donations/services/donations';
import { type Action } from '@/features/pledges/components/pledge-action/pledge-action-types';
import useCurrentUser from '@/hooks/use-current-user';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const DonationAction = ({ donation }: { donation: Donation }) => {
  const [donationAction, setDonationAction] = useState<Action | null>(null);
  const navigate = useNavigate();
  const { isAdmin } = useCurrentUser();

  const { openDialog } = useConsentDialog();

  const updateDonationStatusMutation = useUpdateDonationStatusMutation();
  const deleteDonationMutation = useDeleteDonationMutation();

  const actionAlerts = getActionAlerts();
  const options = prepareActionOptions({ donation, isAdmin });
  const key = prepareMessageKey(donation);

  const actionAlert = donationAction ? actionAlerts.get(donationAction) : null;

  if (!isDefined(key)) {
    return null;
  }

  const headerText = getActionHeaderText().get(key);

  const handleAction = () => {
    if (!isDefined(donationAction)) {
      return;
    }

    switch (donationAction) {
      case 'cancel':
      case 'complete': {
        openDialog({
          title: __('Apply Action', 'growfund'),
          content: (
            <div className="growfund-space-y-3">
              <p>{__('Are you sure you want to apply this action?', 'growfund')}</p>
              <Alert variant="warning">{actionAlerts.get(donationAction)}</Alert>
            </div>
          ),
          confirmText: __('Apply', 'growfund'),
          declineText: __('Cancel', 'growfund'),
          onConfirm: async (closeDialog) => {
            const actionVsPledgeStatusMap = new Map<Action, Donation['status']>([
              ['cancel', 'cancelled'],
              ['complete', 'completed'],
            ]);

            const donationStatus = actionVsPledgeStatusMap.get(donationAction);

            if (!isDefined(donationStatus)) {
              return;
            }

            await updateDonationStatusMutation.mutateAsync({
              id: donation.id,
              status: donationStatus,
            });
            closeDialog();
          },
        });
        break;
      }
      case 'delete':
        openDialog({
          title: __('Delete Pledge', 'growfund'),
          content: (
            <div className="growfund-space-y-3">
              <p>{__('Are you sure you want to delete this pledge?', 'growfund')}</p>
              <Alert variant="warning">{actionAlerts.get('delete')}</Alert>
            </div>
          ),
          confirmText: __('Delete', 'growfund'),
          declineText: __('Cancel', 'growfund'),
          confirmButtonVariant: 'destructive',
          onConfirm: async (closeDialog) => {
            await deleteDonationMutation.mutateAsync({
              id: donation.id,
              is_permanent: true,
            });
            closeDialog();
            void navigate(RouteConfig.Donations.buildLink());
          },
        });
        break;
    }

    setDonationAction(null);
  };

  return (
    <Box>
      <BoxContent className="growfund-space-y-4">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <div className="growfund-flex growfund-items-center growfund-gap-1">
            <DonationActionBadge variant={headerText?.variant} />
            <p className="growfund-typo-tiny growfund-text-fg-secondary">{headerText?.label}</p>
          </div>
        </div>
        <div className="growfund-space-y-2">
          <FormLabel>{__('Take an Action', 'growfund')}</FormLabel>
          <Select
            value={donationAction ?? ''}
            onValueChange={(value) => {
              setDonationAction(value as Action);
            }}
          >
            <SelectTrigger className="[&>span]:growfund-flex [&>span]:growfund-items-center [&_svg]:growfund-size-4 [&_svg]:growfund-text-icon-primary">
              <SelectValue placeholder={__('Select an Action', 'growfund')} />
            </SelectTrigger>
            <SelectContent>
              {options.map((option, index) => {
                if (option.value === 'separator') {
                  return <SelectSeparator key={index} />;
                }
                return (
                  <SelectItem
                    key={index}
                    value={option.value}
                    className={cn(
                      '[&>span]:growfund-flex [&>span]:growfund-items-center [&_svg]:growfund-size-4 [&_svg]:growfund-text-icon-primary',
                      option.is_critical &&
                        'growfund-text-fg-critical [&_svg]:growfund-text-icon-critical',
                    )}
                  >
                    <span className="growfund-mr-2 growfund-text-icon-primary">{option.icon}</span>
                    {option.label}
                  </SelectItem>
                );
              })}
            </SelectContent>
          </Select>
        </div>

        {actionAlert && <Alert>{actionAlert}</Alert>}

        <Button
          className="growfund-w-full growfund-mt-4"
          disabled={!donationAction}
          onClick={handleAction}
        >
          {__('Apply', 'growfund')}
        </Button>
      </BoxContent>
    </Box>
  );
};

export default DonationAction;
