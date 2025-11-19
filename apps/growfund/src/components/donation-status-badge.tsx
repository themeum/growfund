import { __ } from '@wordpress/i18n';
import React from 'react';

import { Badge, type BadgeVariant } from '@/components/ui/badge';
import { type Donation } from '@/features/donations/schemas/donation';

interface DonationStatusBadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  status: Donation['status'];
}

export const DonationStatusBadge = React.forwardRef<HTMLDivElement, DonationStatusBadgeProps>(
  ({ status, ...props }, ref) => {
    const statusMap = new Map<Donation['status'], { label: string; variant: BadgeVariant }>([
      ['pending', { label: __('Pending', 'growfund'), variant: 'warning' }],
      ['completed', { label: __('Completed', 'growfund'), variant: 'primary' }],
      ['cancelled', { label: __('Cancelled', 'growfund'), variant: 'destructive' }],
      ['failed', { label: __('Failed', 'growfund'), variant: 'destructive' }],
      ['refunded', { label: __('Refunded', 'growfund'), variant: 'secondary' }],
    ]);

    return (
      <Badge variant={statusMap.get(status)?.variant} ref={ref} {...props}>
        {statusMap.get(status)?.label}
      </Badge>
    );
  },
);

export default DonationStatusBadge;
