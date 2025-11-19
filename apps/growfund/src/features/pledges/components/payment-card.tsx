import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';

import { Badge } from '@/components/ui/badge';
import { Box, BoxContent } from '@/components/ui/box';
import { Image } from '@/components/ui/image';
import {
    type PaymentStatus,
    type PledgeOption,
    type PledgePayment,
} from '@/features/pledges/schemas/pledge';
import { useCurrency } from '@/hooks/use-currency';
import { isDefined } from '@/utils';

const texts = new Map<keyof PledgePayment, string>([
  ['amount', __('Pledge Amount', 'growfund')],
  ['shipping_cost', __('Shipping', 'growfund')],
  ['bonus_support_amount', __('Bonus', 'growfund')],
  ['recovery_fee', __('Recovery Fee', 'growfund')],
  ['total', __('Total', 'growfund')],
  ['payment_method', __('Payment Method', 'growfund')],
  ['payment_status', __('Payment Status', 'growfund')],
  ['transaction_id', __('Transaction ID', 'growfund')],
]);

const PaymentBadge = ({ status }: { status: PaymentStatus }) => {
  switch (status) {
    case 'paid':
      return <Badge variant="primary">{__('PAID', 'growfund')}</Badge>;
    case 'pending':
    case 'unpaid':
      return <Badge variant="warning">{__('UNPAID', 'growfund')}</Badge>;
    case 'partially-refunded':
      return <Badge variant="special">{__('PARTIALLY REFUNDED', 'growfund')}</Badge>;
    case 'refunded':
      return <Badge variant="secondary">{__('REFUNDED', 'growfund')}</Badge>;
    default:
      return null;
  }
};

const calculateTotalAmount = (payment?: PledgePayment) => {
  if (!isDefined(payment)) {
    return 0;
  }
  const { amount = 0, shipping_cost = 0, bonus_support_amount = 0, recovery_fee = 0 } = payment;
  return (amount ?? 0) + (shipping_cost ?? 0) + (bonus_support_amount ?? 0) + (recovery_fee ?? 0);
};

const PaymentCard = ({
  payment,
  pledgeOption,
}: {
  payment?: PledgePayment;
  pledgeOption?: PledgeOption | null;
}) => {
  const { toCurrency } = useCurrency();
  const totalAmount = calculateTotalAmount(payment);

  useEffect(() => {
    if (!pledgeOption) return;

    if (pledgeOption === 'with-rewards') {
      texts.set('amount', __('Pledge with reward', 'growfund'));
      return;
    }

    texts.set('amount', __('Pledge without a reward', 'growfund'));
  }, [pledgeOption]);

  return (
    <Box>
      <BoxContent>
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary growfund-flex growfund-items-center growfund-gap-2">
            {__('Payment', 'growfund')}
            {!!payment?.payment_method && (
              <div className="growfund-flex growfund-items-center growfund-gap-1">
                <span className="growfund-typo-tiny growfund-text-fg-secondary">{__('via', 'growfund')}</span>
                <Image
                  src={payment.payment_method.logo}
                  alt={payment.payment_method.label}
                  className="growfund-size-4 growfund-border-none growfund-bg-transparent"
                  fit="contain"
                  aspectRatio="square"
                />
                <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                  {payment.payment_method.label}
                </span>
              </div>
            )}
          </h6>
          <PaymentBadge status={payment?.payment_status ?? 'unpaid'} />
        </div>
        <Box className="growfund-mt-3">
          <BoxContent className="growfund-p-3">
            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-text-fg-primary growfund-min-h-7">
              <span className="">{texts.get('amount')}</span>
              <span className="growfund-font-medium">{toCurrency(payment?.amount ?? 0)}</span>
            </div>
            {pledgeOption === 'with-rewards' && (
              <>
                <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-text-fg-primary growfund-min-h-7">
                  <span className="">{texts.get('shipping_cost')}</span>
                  <span className="growfund-font-medium growfund-text-fg-secondary">
                    {toCurrency(payment?.shipping_cost ?? 0)}
                  </span>
                </div>
                <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-text-fg-primary growfund-min-h-7">
                  <span className="">{texts.get('bonus_support_amount')}</span>
                  <span className="growfund-font-medium growfund-text-fg-secondary">
                    {toCurrency(payment?.bonus_support_amount ?? 0)}
                  </span>
                </div>
              </>
            )}

            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-typo-small growfund-font-medium growfund-text-fg-primary growfund-min-h-9">
              <span>{__('Total', 'growfund')}</span>
              <span>{toCurrency(totalAmount)}</span>
            </div>
          </BoxContent>
        </Box>
      </BoxContent>
    </Box>
  );
};

export default PaymentCard;
