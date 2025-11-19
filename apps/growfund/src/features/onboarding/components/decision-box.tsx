import React from 'react';

import { cn } from '@/lib/utils';

const DecisionBox = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-relative growfund-w-full growfund-bg-background-surface growfund-p-8 growfund-rounded-3xl growfund-shadow-xl growfund-overflow-hidden',
          className,
        )}
        {...props}
      >
        {children}
      </div>
    );
  },
);

export default DecisionBox;
