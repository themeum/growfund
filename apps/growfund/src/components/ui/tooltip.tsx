import { InfoCircledIcon } from '@radix-ui/react-icons';
import * as TooltipPrimitive from '@radix-ui/react-tooltip';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const TooltipProvider = TooltipPrimitive.Provider;

const Tooltip = TooltipPrimitive.Root;

const TooltipTrigger = TooltipPrimitive.Trigger;

const Arrow = TooltipPrimitive.Arrow;

interface InfoTooltipProps extends React.HTMLAttributes<HTMLDivElement> {
  iconClassName?: string;
}

const TooltipContent = React.forwardRef<
  React.ComponentRef<typeof TooltipPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof TooltipPrimitive.Content>
>(({ className, sideOffset = 4, ...props }, ref) => (
  <TooltipPrimitive.Portal container={document.getElementById('growfund-root')}>
    <TooltipPrimitive.Content
      ref={ref}
      sideOffset={sideOffset}
      className={cn(
        'growfund-z-dialog growfund-overflow-hidden growfund-rounded-md growfund-bg-background-dark growfund-px-3 growfund-py-1.5 growfund-typo-tiny growfund-text-white growfund-animate-in growfund-fade-in-0 growfund-zoom-in-95 data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=closed]:growfund-zoom-out-95 data-[side=bottom]:growfund-slide-in-from-top-2 data-[side=left]:growfund-slide-in-from-right-2 data-[side=right]:growfund-slide-in-from-left-2 data-[side=top]:growfund-slide-in-from-bottom-2',
        'growfund-max-h-[var(--radix-tooltip-content-available-height)]',
        className,
      )}
      {...props}
    />
  </TooltipPrimitive.Portal>
));
TooltipContent.displayName = TooltipPrimitive.Content.displayName;

const InfoTooltip = React.forwardRef<HTMLDivElement, InfoTooltipProps>(
  ({ children, className, iconClassName = 'growfund-text-icon-primary', ...props }, ref) => {
    return (
      <TooltipProvider>
        <Tooltip {...props} delayDuration={0}>
          <TooltipTrigger asChild>
            <Button variant="ghost" size="icon" className="growfund-size-6" data-type="tooltip">
              <InfoCircledIcon className={iconClassName} />
            </Button>
          </TooltipTrigger>
          <TooltipContent>
            <div ref={ref} className={cn('growfund-max-w-64', className)} {...props}>
              {children}
            </div>
            <Arrow />
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    );
  },
);

InfoTooltip.displayName = 'InfoTooltip';

export { InfoTooltip, Tooltip, TooltipContent, TooltipProvider, TooltipTrigger };
