import { __ } from '@wordpress/i18n';

import { EmptySearchIcon } from '@/app/icons';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { PaymentCard } from '@/features/settings/features/payments/components/payment-card';
import { usePaymentGatewaysQuery } from '@/features/settings/features/payments/services/payment';
import { matchQueryStatus } from '@/utils/match-query-status';

interface PaymentGatewaySelectionDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const PaymentGatewaySelectionDialog = ({
  open,
  onOpenChange,
}: PaymentGatewaySelectionDialogProps) => {
  const paymentGatewaysQuery = usePaymentGatewaysQuery();

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="growfund-max-w-[43.75rem] growfund-max-h-[90svh] growfund-overflow-y-auto">
        <DialogHeader className="growfund-sticky growfund-top-0 growfund-z-header">
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            {__('Payment Gateways', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        {matchQueryStatus(paymentGatewaysQuery, {
          Loading: <LoadingSpinnerOverlay />,
          Error: <div className="growfund-p-4">{__('Error loading payment gateways', 'growfund')}</div>,
          Empty: (
            <div className="growfund-p-4">
              <EmptyState>
                <EmptySearchIcon />
                <EmptyStateDescription>
                  {__(
                    'No payment gateways are currently available. Please try again later.',
                    'growfund',
                  )}
                </EmptyStateDescription>
              </EmptyState>
            </div>
          ),
          Success: ({ data }) => (
            <div className="growfund-p-4 growfund-pt-0 growfund-bg-background-surface-secondary growfund-grid growfund-gap-2">
              {data.map((payment) => (
                <PaymentCard key={payment.name} payment={payment} />
              ))}
            </div>
          ),
        })}

        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Cancel', 'growfund')}
          </Button>
          <Button
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Done', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default PaymentGatewaySelectionDialog;
