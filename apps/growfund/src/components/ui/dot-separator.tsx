import React from 'react';

import { cn } from '@/lib/utils';

const DotSeparator = React.forwardRef<HTMLSpanElement, React.HTMLAttributes<HTMLSpanElement>>(
  ({ className, ...props }, ref) => {
    return (
      <span
        ref={ref}
        {...props}
        className={cn('growfund-size-1 growfund-bg-icon-disabled growfund-rounded-full', className)}
      />
    );
  },
);

DotSeparator.displayName = 'DotSeparator';

export { DotSeparator };
