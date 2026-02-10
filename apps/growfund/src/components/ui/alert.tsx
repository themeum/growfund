import { cva, type VariantProps } from 'class-variance-authority';
import React from 'react';

import { cn } from '@/lib/utils';

const alertVariants = cva(
  'growfund-px-5 growfund-py-3 growfund-border-l-4 growfund-rounded-sm growfund-shadow-sm growfund-typo-small growfund-space-y-2 [&_[data-alert-title]]:growfund-typo-paragraph [&_[data-alert-title]]:growfund-font-medium',
  {
    variants: {
      variant: {
        default:
          'growfund-bg-background-surface-secondary growfund-border-l-border-ring growfund-text-fg-primary',
        warning:
          'growfund-bg-background-fill-warning-secondary growfund-border-l-border-warning growfund-text-fg-caution',
        destructive:
          'growfund-bg-background-fill-critical-secondary growfund-border-l-icon-critical growfund-text-fg-critical',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

type AlertVariants = VariantProps<typeof alertVariants>['variant'];

interface AlertProps
  extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof alertVariants> {}

const Alert = React.forwardRef<HTMLDivElement, AlertProps>(
  ({ children, variant, className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn('growfund-border-border-ring', alertVariants({ variant, className }))}
        {...props}
      >
        {children}
      </div>
    );
  },
);

Alert.displayName = 'Alert';

const AlertTitle = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} data-alert-title className={className} {...props}>
        {children}
      </div>
    );
  },
);

AlertTitle.displayName = 'AlertTitle';

const AlertDescription = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} data-alert-description className={className} {...props}>
        {children}
      </div>
    );
  },
);

AlertDescription.displayName = 'AlertDescription';

export { Alert, AlertDescription, AlertTitle, type AlertVariants };
