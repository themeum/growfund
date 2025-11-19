import { __ } from '@wordpress/i18n';
import { useCallback } from 'react';

import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import { type Donation } from '@/features/donations/schemas/donation';
import { useCurrency } from '@/hooks/use-currency';

const PaymentBadge = ({ status }: { status: 'paid' | 'unpaid' | 'failed' }) => {
  switch (status) {
    case 'paid':
      return <Badge variant="primary">{__('PAID', 'growfund')}</Badge>;
    case 'unpaid':
      return <Badge variant="warning">{__('UNPAID', 'growfund')}</Badge>;
    case 'failed':
      return <Badge variant="destructive">{__('FAILED', 'growfund')}</Badge>;
    default:
      return null;
  }
};

const PaymentView = ({
  amount,
  donationStatus,
  payment_method,
}: {
  amount: number;
  donationStatus: Donation['status'];
  payment_method?: Donation['payment_method'];
}) => {
  const { toCurrency } = useCurrency();

  const getPaymentStatus = useCallback(() => {
    switch (donationStatus) {
      case 'completed':
        return 'paid';
      case 'pending':
        return 'unpaid';
      case 'failed':
      case 'cancelled':
        return 'failed';
      default:
        return 'unpaid';
    }
  }, [donationStatus]);

  return (
    <Box>
      <BoxContent>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-2">
            {__('Payment', 'growfund')}
            {!!payment_method && (
              <div className="growfund-flex growfund-items-center growfund-gap-1">
                <span className="growfund-typo-tiny growfund-text-fg-secondary">{__('via', 'growfund')}</span>
                <Image
                  src={payment_method.logo}
                  alt={payment_method.label}
                  className="growfund-size-4 growfund-border-none growfund-bg-transparent"
                  fit="contain"
                  aspectRatio="square"
                />
                <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                  {payment_method.label}
                </span>
              </div>
            )}
          </h6>
          <PaymentBadge status={getPaymentStatus()} />
        </div>

        <Box className="growfund-mt-3">
          <BoxContent className="growfund-p-3">
            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-text-fg-primary growfund-min-h-7">
              <span>{__('Donation', 'growfund')}</span>
              <span className="growfund-font-medium">{toCurrency(amount)}</span>
            </div>

            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-min-h-9">
              <span>{__('Total', 'growfund')}</span>
              <span>{toCurrency(amount)}</span>
            </div>
          </BoxContent>
        </Box>
      </BoxContent>
    </Box>
  );
};

export default PaymentView;
