import { __ } from '@wordpress/i18n';
import React from 'react';

import { Badge, type BadgeVariant } from '@/components/ui/badge';
import { type Pledge } from '@/features/pledges/schemas/pledge';

interface PledgeStatusBadgeProps extends React.HTMLAttributes<HTMLDivElement> {
  status: Pledge['status'];
}

export const PledgeStatusBadge = React.forwardRef<HTMLDivElement, PledgeStatusBadgeProps>(
  ({ status, ...props }, ref) => {
    const statusMap = new Map<Pledge['status'], { label: string; variant: BadgeVariant }>([
      ['pending', { label: __('Pending', 'growfund'), variant: 'warning' }],
      ['backed', { label: __('Backed', 'growfund'), variant: 'info' }],
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

export default PledgeStatusBadge;
