import * as SelectPrimitive from '@radix-ui/react-select';
import { Check, ChevronDown, ChevronUp } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Select = SelectPrimitive.Root;

const SelectGroup = SelectPrimitive.Group;

const SelectValue = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Value>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Value>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.Value ref={ref} className={cn('growfund-truncate', className)} {...props} />
));
SelectValue.displayName = SelectPrimitive.Value.displayName;

const SelectTrigger = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Trigger>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Trigger>
>(({ className, children, ...props }, ref) => (
  <SelectPrimitive.Trigger
    ref={ref}
    className={cn(
      'growfund-flex growfund-w-full growfund-items-center growfund-justify-between growfund-whitespace-nowrap growfund-rounded-md growfund-border growfund-border-border growfund-bg-transparent growfund-px-3 growfund-py-2 growfund-typo-small growfund-ring-offset-background [&[data-placeholder]]:growfund-text-fg-subdued focus:growfund-outline-none focus:growfund-ring-2 focus:growfund-ring-ring focus:growfund-ring-offset-2 disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50 growfund-h-9 [&>span]:growfund-line-clamp-1',
      className,
    )}
    {...props}
  >
    {children}
    <SelectPrimitive.Icon asChild>
      <ChevronDown className="growfund-size-4 growfund-opacity-50 growfund-ms-2" />
    </SelectPrimitive.Icon>
  </SelectPrimitive.Trigger>
));
SelectTrigger.displayName = SelectPrimitive.Trigger.displayName;

const SelectScrollUpButton = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.ScrollUpButton>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.ScrollUpButton>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.ScrollUpButton
    ref={ref}
    className={cn('growfund-flex growfund-cursor-default growfund-items-center growfund-justify-center growfund-py-1', className)}
    {...props}
  >
    <ChevronUp className="growfund-h-4 growfund-w-4" />
  </SelectPrimitive.ScrollUpButton>
));
SelectScrollUpButton.displayName = SelectPrimitive.ScrollUpButton.displayName;

const SelectScrollDownButton = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.ScrollDownButton>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.ScrollDownButton>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.ScrollDownButton
    ref={ref}
    className={cn('growfund-flex growfund-cursor-default growfund-items-center growfund-justify-center growfund-py-1', className)}
    {...props}
  >
    <ChevronDown className="growfund-h-4 growfund-w-4" />
  </SelectPrimitive.ScrollDownButton>
));
SelectScrollDownButton.displayName = SelectPrimitive.ScrollDownButton.displayName;

const SelectContent = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Content>
>(({ className, children, position = 'popper', ...props }, ref) => (
  <SelectPrimitive.Portal container={document.getElementById('growfund-root')}>
    <SelectPrimitive.Content
      ref={ref}
      className={cn(
        'growfund-relative growfund-z-dialog growfund-max-h-96 growfund-min-w-[8rem] growfund-overflow-hidden growfund-rounded-md growfund-border growfund-bg-popover growfund-text-popover-fg growfund-shadow-md data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0 data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-95 data-[side=bottom]:growfund-slide-in-from-top-2 data-[side=left]:growfund-slide-in-from-right-2 data-[side=right]:growfund-slide-in-from-left-2 data-[side=top]:growfund-slide-in-from-bottom-2',
        position === 'popper' &&
          'data-[side=bottom]:growfund-translate-y-1 data-[side=left]:-growfund-translate-x-1 data-[side=right]:growfund-translate-x-1 data-[side=top]:-growfund-translate-y-1',
        className,
      )}
      position={position}
      {...props}
    >
      <SelectScrollUpButton />
      <SelectPrimitive.Viewport
        className={cn(
          'growfund-p-1',
          position === 'popper' &&
            'growfund-h-[var(--radix-select-trigger-height)] growfund-w-full growfund-min-w-[var(--radix-select-trigger-width)]',
        )}
      >
        {children}
      </SelectPrimitive.Viewport>
      <SelectScrollDownButton />
    </SelectPrimitive.Content>
  </SelectPrimitive.Portal>
));
SelectContent.displayName = SelectPrimitive.Content.displayName;

const SelectLabel = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Label>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Label>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.Label
    ref={ref}
    className={cn('growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-font-semibold', className)}
    {...props}
  />
));
SelectLabel.displayName = SelectPrimitive.Label.displayName;

const SelectItem = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Item>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Item>
>(({ className, children, ...props }, ref) => (
  <SelectPrimitive.Item
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-w-full growfund-cursor-default growfund-select-none growfund-items-center growfund-rounded-sm growfund-py-1.5 growfund-pl-2 growfund-pr-8 growfund-typo-small growfund-outline-none focus:growfund-bg-accent focus:growfund-text-fg-accent data-[disabled]:growfund-pointer-events-none data-[disabled]:growfund-opacity-50',
      className,
    )}
    {...props}
  >
    <span className="growfund-absolute growfund-right-2 growfund-flex growfund-h-3.5 growfund-w-3.5 growfund-items-center growfund-justify-center">
      <SelectPrimitive.ItemIndicator>
        <Check className="growfund-h-4 growfund-w-4" />
      </SelectPrimitive.ItemIndicator>
    </span>
    <SelectPrimitive.ItemText>{children}</SelectPrimitive.ItemText>
  </SelectPrimitive.Item>
));
SelectItem.displayName = SelectPrimitive.Item.displayName;

const SelectSeparator = React.forwardRef<
  React.ComponentRef<typeof SelectPrimitive.Separator>,
  React.ComponentPropsWithoutRef<typeof SelectPrimitive.Separator>
>(({ className, ...props }, ref) => (
  <SelectPrimitive.Separator
    ref={ref}
    className={cn('growfund--mx-1 growfund-my-1 growfund-h-px growfund-bg-border', className)}
    {...props}
  />
));
SelectSeparator.displayName = SelectPrimitive.Separator.displayName;

export {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectScrollDownButton,
    SelectScrollUpButton,
    SelectSeparator,
    SelectTrigger,
    SelectValue
};

