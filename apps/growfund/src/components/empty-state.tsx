import React from 'react';

import { Box } from '@/components/ui/box';
import { cn } from '@/lib/utils';

const EmptyState = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, children, ...props }, ref) => {
    return (
      <Box
        className={cn(
          'growfund-shadow-sm growfund-border-none growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-gap-2 growfund-pt-6 growfund-pb-12 growfund-px-6',
          className,
        )}
        ref={ref}
        {...props}
      >
        {children}
      </Box>
    );
  },
);

EmptyState.displayName = 'EmptyState';

const EmptyStateDescription = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, children, ...props }, ref) => {
  return (
    <div
      className={cn('growfund-typo-small growfund-text-fg-secondary growfund-text-center', className)}
      ref={ref}
      {...props}
    >
      {children}
    </div>
  );
});

EmptyStateDescription.displayName = 'EmptyStateDescription';

export { EmptyState, EmptyStateDescription };
