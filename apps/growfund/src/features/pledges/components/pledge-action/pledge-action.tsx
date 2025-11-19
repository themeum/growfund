import { __ } from '@wordpress/i18n';
import { useEffect, useState } from 'react';
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
import { PledgeActionBadge } from '@/features/pledges/components/pledge-action/pledge-action-badge';
import {
    getActionAlerts,
    getActionHeaderText,
    prepareActionOptions,
    prepareMessageKey,
} from '@/features/pledges/components/pledge-action/pledge-action-services';
import { type Action } from '@/features/pledges/components/pledge-action/pledge-action-types';
import { type Pledge } from '@/features/pledges/schemas/pledge';
import {
    useChargePledgedBackerMutation,
    useDeletePledgeMutation,
    useRetryFailedPaymentMutation,
    useUpdatePledgeStatusMutation,
} from '@/features/pledges/services/pledges';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const PledgeAction = ({ pledge }: { pledge: Pledge }) => {
  const [pledgeAction, setPledgeAction] = useState<Action | null>(null);
  const navigate = useNavigate();

  const { openDialog } = useConsentDialog();

  const updatePledgeStatusMutation = useUpdatePledgeStatusMutation();
  const chargePledgedBackerMutation = useChargePledgedBackerMutation();
  const retryFailedPaymentMutation = useRetryFailedPaymentMutation();
  const deletePledgeMutation = useDeletePledgeMutation();

  const actionAlerts = getActionAlerts();
  const options = prepareActionOptions(pledge);
  const key = prepareMessageKey(pledge);

  useEffect(() => {
    if (pledge.status === 'in-progress') {
      setPledgeAction('in-progress');
    }
  }, [pledge.status]);

  const actionAlert = pledgeAction ? actionAlerts.get(pledgeAction) : null;

  if (!isDefined(key)) {
    return null;
  }

  const headerText = getActionHeaderText().get(key);

  const handleAction = () => {
    if (!isDefined(pledgeAction)) {
      return;
    }

    switch (pledgeAction) {
      case 'charge-backer':
        openDialog({
          title: __('Charge Backer', 'growfund'),
          content: (
            <div className="growfund-space-y-3">
              <p>{__('Are you sure you want to charge this backer?', 'growfund')}</p>
              <Alert variant="warning">{actionAlerts.get('charge-backer')}</Alert>
            </div>
          ),
          confirmText: __('Charge', 'growfund'),
          declineText: __('Cancel', 'growfund'),
          onConfirm: async (closeDialog) => {
            await chargePledgedBackerMutation.mutateAsync({ pledgeId: pledge.id });
            closeDialog();
          },
        });
        break;
      case 'retry':
        openDialog({
          title: __('Retry Payment', 'growfund'),
          content: (
            <div className="growfund-space-y-3">
              <p>{__('Are you sure you want to retry this payment?', 'growfund')}</p>
              <Alert variant="warning">{actionAlerts.get('retry')}</Alert>
            </div>
          ),
          confirmText: __('Retry', 'growfund'),
          declineText: __('Cancel', 'growfund'),
          onConfirm: async (closeDialog) => {
            await retryFailedPaymentMutation.mutateAsync({ pledgeId: pledge.id });
            closeDialog();
          },
        });
        break;
      case 'cancel':
      case 'complete':
      case 'mark-as-backed': {
        openDialog({
          title: __('Apply Action', 'growfund'),
          content: (
            <div className="growfund-space-y-3">
              <p>{__('Are you sure you want to apply this action?', 'growfund')}</p>
              <Alert variant="warning">{actionAlerts.get(pledgeAction)}</Alert>
            </div>
          ),
          confirmText: __('Apply', 'growfund'),
          declineText: __('Cancel', 'growfund'),
          onConfirm: async (closeDialog) => {
            const actionVsPledgeStatusMap = new Map<Action, Pledge['status']>([
              ['cancel', 'cancelled'],
              ['complete', 'completed'],
              ['mark-as-backed', 'backed'],
            ]);

            const pledgeStatus = actionVsPledgeStatusMap.get(pledgeAction);

            if (!isDefined(pledgeStatus)) {
              return;
            }

            await updatePledgeStatusMutation.mutateAsync({
              id: pledge.id,
              status: pledgeStatus,
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
            await deletePledgeMutation.mutateAsync({ id: pledge.id, is_permanent: true });
            closeDialog();
            void navigate(RouteConfig.Pledges.buildLink());
          },
        });
        break;
    }

    setPledgeAction(null);
  };

  return (
    <Box>
      <BoxContent className="growfund-space-y-4">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <div className="growfund-flex growfund-items-center growfund-gap-1">
            <PledgeActionBadge variant={headerText?.variant} />
            <p className="growfund-typo-tiny growfund-text-fg-secondary">{headerText?.label}</p>
          </div>
        </div>
        {pledge.status !== 'in-progress' && (
          <div className="growfund-space-y-2">
            <FormLabel>{__('Take an Action', 'growfund')}</FormLabel>
            <Select
              value={pledgeAction ?? ''}
              onValueChange={(value) => {
                setPledgeAction(value as Action);
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
                        option.is_critical && 'growfund-text-fg-critical [&_svg]:growfund-text-icon-critical',
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
        )}

        {actionAlert && <Alert>{actionAlert}</Alert>}

        {pledge.status !== 'in-progress' && (
          <Button className="growfund-w-full growfund-mt-4" disabled={!pledgeAction} onClick={handleAction}>
            {__('Apply', 'growfund')}
          </Button>
        )}
      </BoxContent>
    </Box>
  );
};

export default PledgeAction;
