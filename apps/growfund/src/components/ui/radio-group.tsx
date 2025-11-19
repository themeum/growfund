import * as RadioGroupPrimitive from '@radix-ui/react-radio-group';
import { Circle } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const RadioGroup = React.forwardRef<
  React.ComponentRef<typeof RadioGroupPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof RadioGroupPrimitive.Root>
>(({ className, ...props }, ref) => {
  return (
    <RadioGroupPrimitive.Root className={cn('growfund-grid growfund-gap-2', className)} {...props} ref={ref} />
  );
});
RadioGroup.displayName = RadioGroupPrimitive.Root.displayName;

const RadioGroupItem = React.forwardRef<
  React.ComponentRef<typeof RadioGroupPrimitive.Item>,
  React.ComponentPropsWithoutRef<typeof RadioGroupPrimitive.Item>
>(({ className, ...props }, ref) => {
  return (
    <RadioGroupPrimitive.Item
      ref={ref}
      className={cn(
        'growfund-aspect-square growfund-h-4 growfund-w-4 growfund-rounded-full growfund-border growfund-border-icon-primary growfund-text-fg-primary focus:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2 disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50 data-[state=checked]:growfund-border-background-fill-brand',
        className,
      )}
      {...props}
    >
      <RadioGroupPrimitive.Indicator className="growfund-flex growfund-items-center growfund-justify-center">
        <Circle className="growfund-h-3 growfund-w-3 growfund-border-0 growfund-fill-background-fill-brand growfund-stroke-none" />
      </RadioGroupPrimitive.Indicator>
    </RadioGroupPrimitive.Item>
  );
});
RadioGroupItem.displayName = RadioGroupPrimitive.Item.displayName;

export { RadioGroup, RadioGroupItem };
