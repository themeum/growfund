import { BadgeCheck, CircleDashed, CircleDollarSignIcon } from 'lucide-react';

import { type ActionVariant } from '@/features/pledges/components/pledge-action/pledge-action-types';
import { cn } from '@/lib/utils';

const DonationActionBadge = ({ variant }: { variant?: ActionVariant }) => {
  if (!variant) {
    return null;
  }
  switch (variant) {
    case 'success':
      return (
        <div className="growfund-size-4 growfund-rounded-full growfund-bg-background-fill-success-secondary growfund-flex growfund-items-center growfund-justify-center">
          <span className={cn('growfund-size-[6px] growfund-rounded-full growfund-bg-background-fill-brand')} />
        </div>
      );
    case 'backed':
      return <CircleDollarSignIcon className="growfund-size-4 growfund-text-icon-success" />;
    case 'completed':
      return <BadgeCheck className="growfund-text-white growfund-fill-fg-brand growfund-size-4" />;
    case 'in-progress':
      return <CircleDashed className="growfund-size-4 growfund-text-border-warning" />;
    case 'critical':
      return (
        <div className="growfund-size-4 growfund-rounded-full growfund-bg-background-fill-critical-secondary growfund-flex growfund-items-center growfund-justify-center">
          <span className={cn('growfund-size-[6px] growfund-rounded-full growfund-bg-background-fill-critical')} />
        </div>
      );
  }
};

export { DonationActionBadge };
