import { __ } from '@wordpress/i18n';
import * as React from 'react';

import { cn } from '@/lib/utils';

const ProBadge = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    return (
      <div
        className={cn(
          'growfund-pro-badge',
          className,
        )}
        {...props}
        ref={ref}
      >
        {__('Pro', 'growfund')}
      </div>
    );
  },
);

export { ProBadge };
