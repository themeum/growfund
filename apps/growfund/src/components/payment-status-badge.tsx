import { __ } from '@wordpress/i18n';
import React from 'react';

import { Badge, type BadgeVariant } from '@/components/ui/badge';
import { type PaymentStatus } from '@/features/pledges/schemas/pledge';

interface PaymentStatusBadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  status: PaymentStatus;
}

const PaymentStatusBadge = React.forwardRef<HTMLDivElement, PaymentStatusBadgeProps>(
  ({ status, ...props }, ref) => {
    const statusMap = new Map<PaymentStatus, { label: string; variant: BadgeVariant }>([
      ['pending', { label: __('Pending', 'growfund'), variant: 'warning' }],
      ['failed', { label: __('Failed', 'growfund'), variant: 'destructive' }],
      ['refunded', { label: __('Refunded', 'growfund'), variant: 'secondary' }],
      ['paid', { label: __('Paid', 'growfund'), variant: 'primary' }],
      ['unpaid', { label: __('Unpaid', 'growfund'), variant: 'warning' }],
    ]);

    if (!statusMap.has(status)) {
      return null;
    }

    return (
      <Badge variant={statusMap.get(status)?.variant} {...props} ref={ref}>
        {statusMap.get(status)?.label}
      </Badge>
    );
  },
);

export default PaymentStatusBadge;
