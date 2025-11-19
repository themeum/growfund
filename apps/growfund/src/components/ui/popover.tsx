import * as PopoverPrimitive from '@radix-ui/react-popover';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Popover = PopoverPrimitive.Root;

const PopoverTrigger = PopoverPrimitive.Trigger;

const PopoverAnchor = PopoverPrimitive.Anchor;

const PopoverPortal = PopoverPrimitive.Portal;

const PopoverContent = React.forwardRef<
  React.ComponentRef<typeof PopoverPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof PopoverPrimitive.Content>
>(({ className, align = 'center', sideOffset = 4, ...props }, ref) => (
  <PopoverPrimitive.Portal container={document.getElementById('growfund-root')}>
    <PopoverPrimitive.Content
      ref={ref}
      align={align}
      sideOffset={sideOffset}
      className={cn(
        'growfund-z-popover growfund-w-72 growfund-rounded-md growfund-border growfund-bg-background-surface growfund-p-4 growfund-text-fg-primary growfund-shadow-md growfund-outline-none data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0 data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-95 data-[side=bottom]:growfund-slide-in-from-top-2 data-[side=left]:growfund-slide-in-from-right-2 data-[side=right]:growfund-slide-in-from-left-2 data-[side=top]:growfund-slide-in-from-bottom-2',
        className,
      )}
      {...props}
    />
  </PopoverPrimitive.Portal>
));
PopoverContent.displayName = PopoverPrimitive.Content.displayName;

export { Popover, PopoverAnchor, PopoverContent, PopoverPortal, PopoverTrigger };
