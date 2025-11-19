import * as ScrollAreaPrimitive from '@radix-ui/react-scroll-area';
import * as React from 'react';

import { cn } from '@/lib/utils';

const ScrollArea = React.forwardRef<
  React.ComponentRef<typeof ScrollAreaPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof ScrollAreaPrimitive.Root>
>(({ className, children, ...props }, ref) => (
  <ScrollAreaPrimitive.Root
    ref={ref}
    className={cn(
      'growfund-relative growfund-overflow-hidden [&_[data-radix-scroll-area-viewport]>div]:growfund-h-full',
      className,
    )}
    {...props}
  >
    <ScrollAreaPrimitive.Viewport className="growfund-h-full growfund-w-full growfund-rounded-[inherit]">
      {children}
    </ScrollAreaPrimitive.Viewport>
    <ScrollBar />
    <ScrollAreaPrimitive.Corner />
  </ScrollAreaPrimitive.Root>
));
ScrollArea.displayName = ScrollAreaPrimitive.Root.displayName;

const ScrollBar = React.forwardRef<
  React.ComponentRef<typeof ScrollAreaPrimitive.ScrollAreaScrollbar>,
  React.ComponentPropsWithoutRef<typeof ScrollAreaPrimitive.ScrollAreaScrollbar>
>(({ className, orientation = 'vertical', ...props }, ref) => (
  <ScrollAreaPrimitive.ScrollAreaScrollbar
    ref={ref}
    orientation={orientation}
    className={cn(
      'growfund-flex growfund-touch-none growfund-select-none growfund-transition-colors',
      orientation === 'vertical' &&
        'growfund-h-full growfund-w-2.5 growfund-border-l growfund-border-l-transparent growfund-p-[1px]',
      orientation === 'horizontal' &&
        'growfund-h-2.5 growfund-flex-col growfund-border-t growfund-border-t-transparent growfund-p-[1px]',
      className,
    )}
    {...props}
  >
    <ScrollAreaPrimitive.ScrollAreaThumb className="growfund-relative growfund-flex-1 growfund-rounded-full growfund-bg-border" />
  </ScrollAreaPrimitive.ScrollAreaScrollbar>
));
ScrollBar.displayName = ScrollAreaPrimitive.ScrollAreaScrollbar.displayName;

export { ScrollArea, ScrollBar };
