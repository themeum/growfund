import * as ProgressPrimitive from '@radix-ui/react-progress';
import { cva, type VariantProps } from 'class-variance-authority';
import * as React from 'react';

import { cn } from '@/lib/utils';

const progressVariants = cva(
  'growfund-relative growfund-h-2 growfund-w-full growfund-overflow-hidden growfund-rounded-full growfund-bg-background-fill-tertiary',
  {
    variants: {
      size: {
        default: 'growfund-h-2',
        sm: 'growfund-h-1',
        lg: 'growfund-h-3',
      },
    },
    defaultVariants: {
      size: 'default',
    },
  },
);

const Progress = React.forwardRef<
  React.ComponentRef<typeof ProgressPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof ProgressPrimitive.Root> &
    VariantProps<typeof progressVariants>
>(({ className, value, size = 'default', ...props }, ref) => (
  <ProgressPrimitive.Root
    ref={ref}
    className={cn(progressVariants({ size }), className)}
    {...props}
  >
    <ProgressPrimitive.Indicator
      className="growfund-h-full growfund-w-full growfund-flex-1 growfund-bg-background-fill-brand growfund-transition-all growfund-rounded-full"
      style={{ transform: `translateX(-${String(100 - (value ?? 0))}%)` }}
    />
  </ProgressPrimitive.Root>
));
Progress.displayName = ProgressPrimitive.Root.displayName;

export { Progress };
