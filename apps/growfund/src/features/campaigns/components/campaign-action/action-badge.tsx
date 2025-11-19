import { BadgeCheck, EyeOff, PartyPopper } from 'lucide-react';

import { EndFlagIcon } from '@/app/icons';
import { type ActionVariant } from '@/features/campaigns/components/campaign-action/campaign-action-types';
import { cn } from '@/lib/utils';

const ActionBadge = ({ variant }: { variant?: ActionVariant }) => {
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
    case 'secondary':
      return (
        <div className="growfund-size-4 growfund-rounded-full growfund-bg-background-fill-secondary-hover growfund-flex growfund-items-center growfund-justify-center">
          <span className={cn('growfund-size-[6px] growfund-rounded-full growfund-bg-background-fill-disabled')} />
        </div>
      );
    case 'ended':
      return <EndFlagIcon className="growfund-size-4" />;
    case 'completed':
      return <BadgeCheck className="growfund-text-white growfund-fill-fg-brand growfund-size-4" />;
    case 'funded':
      return <PartyPopper className="growfund-text-icon-brand growfund-size-4" />;
    case 'hidden':
      return <EyeOff className="growfund-text-icon-primary growfund-size-4" />;
  }
};

export { ActionBadge };
