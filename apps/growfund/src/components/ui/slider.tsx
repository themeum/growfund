import * as SliderPrimitive from '@radix-ui/react-slider';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Slider = React.forwardRef<
  React.ComponentRef<typeof SliderPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof SliderPrimitive.Root>
>(({ className, ...props }, ref) => (
  <SliderPrimitive.Root
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-w-full growfund-touch-none growfund-select-none growfund-items-center',
      className,
    )}
    {...props}
  >
    <SliderPrimitive.Track className="growfund-relative growfund-h-1.5 growfund-w-full growfund-grow growfund-overflow-hidden growfund-rounded-full growfund-bg-background-fill-tertiary">
      <SliderPrimitive.Range className="growfund-absolute growfund-h-full growfund-bg-background-fill-brand" />
    </SliderPrimitive.Track>
    <SliderPrimitive.Thumb className="growfund-block growfund-h-4 growfund-w-4 growfund-rounded-full growfund-border growfund-border-border-hover growfund-bg-background-surface growfund-transition-colors focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-offset-2 focus-visible:growfund-ring-ring disabled:growfund-pointer-events-none disabled:growfund-opacity-50" />
  </SliderPrimitive.Root>
));
Slider.displayName = SliderPrimitive.Root.displayName;

export { Slider };
