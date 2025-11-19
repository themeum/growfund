import * as SwitchPrimitives from '@radix-ui/react-switch';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Switch = React.forwardRef<
  React.ComponentRef<typeof SwitchPrimitives.Root>,
  React.ComponentPropsWithoutRef<typeof SwitchPrimitives.Root>
>(({ className, value, ...props }, ref) => (
  <SwitchPrimitives.Root
    className={cn(
      'peer growfund-inline-flex growfund-h-5 growfund-w-9 growfund-shrink-0 growfund-cursor-pointer growfund-items-center growfund-rounded-full growfund-border-2 growfund-border-transparent growfund-transition-colors disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50',
      'focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2',
      'data-[state=checked]:growfund-bg-background-fill-brand data-[state=unchecked]:growfund-bg-background-fill-tertiary',
      className,
    )}
    {...props}
    value={value ?? undefined}
    ref={ref}
  >
    <SwitchPrimitives.Thumb
      className={cn(
        'growfund-pointer-events-none growfund-block growfund-h-4 growfund-w-4 growfund-rounded-full growfund-bg-white growfund-ring-0 growfund-transition-transform data-[state=checked]:growfund-translate-x-4 data-[state=unchecked]:growfund-translate-x-0',
      )}
    />
  </SwitchPrimitives.Root>
));
Switch.displayName = SwitchPrimitives.Root.displayName;

export { Switch };
