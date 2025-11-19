import * as CheckboxPrimitive from '@radix-ui/react-checkbox';
import { CheckIcon } from '@radix-ui/react-icons';
import { MinusIcon } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Checkbox = React.forwardRef<
  React.ComponentRef<typeof CheckboxPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof CheckboxPrimitive.Root>
>(({ className, ...props }, ref) => {
  return (
    <CheckboxPrimitive.Root
      ref={ref}
      className={cn(
        'peer growfund-h-4 growfund-w-4 growfund-shrink-0 growfund-rounded-sm growfund-border growfund-border-border disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50 growfund-ring-offset-background',
        'focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2',
        'data-[state=checked]:growfund-bg-background-fill-brand data-[state=checked]:growfund-border-border-brand data-[state=checked]:growfund-text-fg-light',
        'data-[state=indeterminate]:growfund-bg-background-fill-brand data-[state=indeterminate]:growfund-border-border-brand data-[state=indeterminate]:growfund-text-fg-light',
        className,
      )}
      {...props}
    >
      <CheckboxPrimitive.Indicator
        className={cn('growfund-flex growfund-items-center growfund-justify-center growfund-text-current')}
      >
        {props.checked === 'indeterminate' ? (
          <MinusIcon className="growfund-h-[0.875rem] growfund-w-[0.875rem]" strokeWidth={3} />
        ) : (
          <CheckIcon className="growfund-h-[0.875rem] growfund-w-[0.875rem]" />
        )}
      </CheckboxPrimitive.Indicator>
    </CheckboxPrimitive.Root>
  );
});
Checkbox.displayName = CheckboxPrimitive.Root.displayName;

export { Checkbox };
