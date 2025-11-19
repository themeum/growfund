import React from 'react';

import { cn } from '@/lib/utils';

const TableCard = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-border growfund-border-border growfund-bg-background-white growfund-rounded-md growfund-overflow-hidden',
          className,
        )}
        {...props}
      >
        {children}
      </div>
    );
  },
);

TableCard.displayName = 'TableCard';

const TableCardHeader = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-bg-background-white growfund-p-3 growfund-border-b growfund-border-b-border growfund-flex',
          className,
        )}
        {...props}
      >
        {children}
      </div>
    );
  },
);

TableCardHeader.displayName = 'TableCardHeader';

const TableCardContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} className={cn('growfund-p-4', className)} {...props}>
        {children}
      </div>
    );
  },
);

TableCardContent.displayName = 'TableCardContent';

export { TableCard, TableCardContent, TableCardHeader };
