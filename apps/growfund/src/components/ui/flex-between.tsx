import React from 'react';

import { cn } from '@/lib/utils';

const FlexBetween = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn('growfund-flex growfund-items-center growfund-justify-between', className)}
        {...props}
      />
    );
  },
);

FlexBetween.displayName = 'FlexBetween';

export { FlexBetween };
