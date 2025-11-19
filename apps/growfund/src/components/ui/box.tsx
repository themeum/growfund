import React from 'react';

import { cn } from '@/lib/utils';

const Box = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-bg-background-surface growfund-rounded-md growfund-shadow-sm growfund-border growfund-border-border growfund-group/box',
          className,
        )}
        {...props}
      />
    );
  },
);

Box.displayName = 'Box';

const BoxTitle = React.forwardRef<HTMLHeadingElement, React.HTMLAttributes<HTMLHeadingElement>>(
  ({ className, ...props }, ref) => {
    return (
      <h6
        ref={ref}
        className={cn(
          'growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary growfund-w-full growfund-flex growfund-items-center growfund-gap-2 [&>[data-type=tooltip]]:growfund-opacity-0 group-hover/box:[&>[data-type=tooltip]]:growfund-opacity-100 growfund-transition-opacity',
          className,
        )}
        {...props}
      />
    );
  },
);

BoxTitle.displayName = 'BoxTitle';

const BoxContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    return <div ref={ref} className={cn('growfund-p-4 growfund-w-full', className)} {...props} />;
  },
);

BoxContent.displayName = 'BoxContent';

export { Box, BoxContent, BoxTitle };
