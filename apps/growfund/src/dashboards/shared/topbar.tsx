import React from 'react';

import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import { cn } from '@/lib/utils';

const Topbar = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    const { topbar } = useDashboardLayoutContext();
    const TopbarIcon = topbar.icon;

    return (
      <div
        data-topbar-container
        ref={ref}
        className={cn(
          'growfund-w-full growfund-sticky growfund-z-header growfund-border-b growfund-border-b-border growfund-bg-background-surface-secondary growfund-max-h-[var(--growfund-topbar-height)] growfund-min-h-[var(--growfund-topbar-height)] growfund-top-0 growfund-px-7 growfund-flex growfund-items-center growfund-justify-between',
          className,
        )}
        {...props}
      >
        <div className="growfund-flex growfund-items-center growfund-gap-2">
          <TopbarIcon className="growfund-size-5 growfund-text-icon-primary" />
          <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{topbar.title}</span>
        </div>
      </div>
    );
  },
);

export default Topbar;
