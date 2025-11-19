import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { cn } from '@/lib/utils';

const badgeVariants = cva(
  'growfund-inline-flex growfund-gap-1 growfund-items-center growfund-rounded-md growfund-border growfund-px-2 growfund-py-1 growfund-typo-tiny growfund-transition-colors focus:growfund-outline-none focus:growfund-ring-2 focus:growfund-ring-ring focus:growfund-ring-offset-2 growfund-max-h-[1.5rem]',
  {
    variants: {
      variant: {
        primary:
          'growfund-border-transparent growfund-bg-background-fill-success-secondary growfund-text-fg-success hover:growfund-bg-background-fill-success-secondary/80',
        secondary:
          'growfund-border-transparent growfund-bg-background-surface-secondary growfund-bg-border growfund-text-fg-primary hover:growfund-bg-border/80',
        destructive:
          'growfund-border-transparent growfund-bg-background-fill-critical-secondary growfund-text-fg-critical hover:growfund-bg-background-fill-critical-secondary/80',
        warning:
          'growfund-border-transparent growfund-bg-background-fill-warning-secondary growfund-text-fg-warning hover:growfund-bg-background-fill-warning-secondary/80',
        info: 'growfund-border-transparent growfund-bg-background-fill-special-secondary growfund-text-fg-special-2 hover:growfund-bg-background-fill-special-secondary/80',
        special:
          'growfund-border-transparent growfund-bg-background-fill-special-2-secondary growfund-text-fg-special-3 hover:growfund-bg-background-fill-special-2-secondary/80',
        outline: 'growfund-text-fg-primary',
        ghost: 'growfund-border-none growfund-bg-transparent growfund-text-fg-primary',
      },
    },
    defaultVariants: {
      variant: 'primary',
    },
  },
);

type BadgeVariant = VariantProps<typeof badgeVariants>['variant'];

export interface BadgeProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof badgeVariants> {}

const Badge = React.forwardRef<HTMLDivElement, BadgeProps>(
  ({ className, variant, ...props }, ref) => {
    return <div className={cn(badgeVariants({ variant }), className)} {...props} ref={ref} />;
  },
);

// eslint-disable-next-line react-refresh/only-export-components
export { Badge, badgeVariants, type BadgeVariant };
