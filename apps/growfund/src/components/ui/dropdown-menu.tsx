import * as DropdownMenuPrimitive from '@radix-ui/react-dropdown-menu';
import { Check, ChevronRight, Circle } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const DropdownMenu = DropdownMenuPrimitive.Root;

const DropdownMenuTrigger = DropdownMenuPrimitive.Trigger;

const DropdownMenuGroup = DropdownMenuPrimitive.Group;

const DropdownMenuPortal = DropdownMenuPrimitive.Portal;

const DropdownMenuSub = DropdownMenuPrimitive.Sub;

const DropdownMenuRadioGroup = DropdownMenuPrimitive.RadioGroup;

const DropdownMenuSubTrigger = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.SubTrigger>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.SubTrigger> & {
    inset?: boolean;
  }
>(({ className, inset, children, ...props }, ref) => (
  <DropdownMenuPrimitive.SubTrigger
    ref={ref}
    className={cn(
      'growfund-flex growfund-cursor-default growfund-select-none growfund-items-center growfund-gap-2 growfund-rounded-sm growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-outline-none focus:growfund-bg-accent data-[state=open]:growfund-bg-accent [&_svg]:growfund-pointer-events-none [&_svg]:growfund-size-4 [&_svg]:growfund-shrink-0',
      inset && 'growfund-pl-8',
      className,
    )}
    {...props}
  >
    {children}
    <ChevronRight className="growfund-ml-auto" />
  </DropdownMenuPrimitive.SubTrigger>
));
DropdownMenuSubTrigger.displayName = DropdownMenuPrimitive.SubTrigger.displayName;

const DropdownMenuSubContent = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.SubContent>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.SubContent>
>(({ className, ...props }, ref) => (
  <DropdownMenuPrimitive.SubContent
    ref={ref}
    className={cn(
      'growfund-z-50 growfund-min-w-[8rem] growfund-overflow-hidden growfund-rounded-md growfund-border growfund-bg-popover growfund-p-1 growfund-text-fg-primary growfund-shadow-lg data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0 data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-95 data-[side=bottom]:growfund-slide-in-from-top-2 data-[side=left]:growfund-slide-in-from-right-2 data-[side=right]:growfund-slide-in-from-left-2 data-[side=top]:growfund-slide-in-from-bottom-2 growfund-origin-[--radix-dropdown-menu-content-transform-origin]',
      className,
    )}
    {...props}
  />
));
DropdownMenuSubContent.displayName = DropdownMenuPrimitive.SubContent.displayName;

const DropdownMenuContent = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Content>
>(({ className, sideOffset = 4, ...props }, ref) => (
  <DropdownMenuPrimitive.Portal container={document.getElementById('growfund-root')}>
    <DropdownMenuPrimitive.Content
      ref={ref}
      sideOffset={sideOffset}
      className={cn(
        'growfund-z-50 growfund-max-h-[var(--radix-dropdown-menu-content-available-height)] growfund-min-w-[8rem] growfund-overflow-y-auto growfund-overflow-x-hidden growfund-rounded-md growfund-border growfund-bg-popover growfund-p-1 growfund-text-popover-foreground growfund-shadow-md',
        'data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0 data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-95 data-[side=bottom]:growfund-slide-in-from-top-2 data-[side=left]:growfund-slide-in-from-right-2 data-[side=right]:growfund-slide-in-from-left-2 data-[side=top]:growfund-slide-in-from-bottom-2 growfund-origin-[--radix-dropdown-menu-content-transform-origin]',
        className,
      )}
      {...props}
    />
  </DropdownMenuPrimitive.Portal>
));
DropdownMenuContent.displayName = DropdownMenuPrimitive.Content.displayName;

const DropdownMenuItem = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.Item>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Item> & {
    inset?: boolean;
  }
>(({ className, inset, ...props }, ref) => (
  <DropdownMenuPrimitive.Item
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-cursor-default growfund-select-none growfund-items-center growfund-gap-2 growfund-rounded-sm growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-outline-none growfund-transition-colors focus:growfund-bg-accent focus:growfund-text-accent-foreground   data-[disabled]:growfund-pointer-events-none data-[disabled]:growfund-opacity-50 [&>svg]:growfund-size-4 [&>svg]:growfund-shrink-0',
      inset && 'growfund-pl-8',
      className,
    )}
    {...props}
  />
));
DropdownMenuItem.displayName = DropdownMenuPrimitive.Item.displayName;

const DropdownMenuCheckboxItem = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.CheckboxItem>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.CheckboxItem>
>(({ className, children, checked, ...props }, ref) => (
  <DropdownMenuPrimitive.CheckboxItem
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-cursor-default growfund-select-none growfund-items-center growfund-rounded-sm growfund-py-1.5 growfund-pl-8 growfund-pr-2 growfund-typo-small growfund-outline-none growfund-transition-colors growfund-focus:growfund-bg-accent growfund-focus:growfund-text-accent-foreground data-[disabled]:growfund-pointer-events-none data-[disabled]:growfund-opacity-50',
      className,
    )}
    checked={checked}
    {...props}
  >
    <span className="growfund-absolute growfund-left-2 growfund-flex growfund-h-3.5 growfund-w-3.5 growfund-items-center growfund-justify-center">
      <DropdownMenuPrimitive.ItemIndicator>
        <Check className="growfund-h-4 growfund-w-4" />
      </DropdownMenuPrimitive.ItemIndicator>
    </span>
    {children}
  </DropdownMenuPrimitive.CheckboxItem>
));
DropdownMenuCheckboxItem.displayName = DropdownMenuPrimitive.CheckboxItem.displayName;

const DropdownMenuRadioItem = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.RadioItem>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.RadioItem>
>(({ className, children, ...props }, ref) => (
  <DropdownMenuPrimitive.RadioItem
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-cursor-default growfund-select-none growfund-items-center growfund-rounded-sm growfund-py-1.5 growfund-pl-8 growfund-pr-2 growfund-typo-small growfund-outline-none growfund-transition-colors growfund-focus:growfund-bg-accent growfund-focus:growfund-text-accent-foreground data-[disabled]:growfund-pointer-events-none data-[disabled]:growfund-opacity-50',
      className,
    )}
    {...props}
  >
    <span className="growfund-absolute growfund-left-2 growfund-flex growfund-h-3.5 growfund-w-3.5 growfund-items-center growfund-justify-center">
      <DropdownMenuPrimitive.ItemIndicator>
        <Circle className="growfund-h-2 growfund-w-2 growfund-fill-current" />
      </DropdownMenuPrimitive.ItemIndicator>
    </span>
    {children}
  </DropdownMenuPrimitive.RadioItem>
));
DropdownMenuRadioItem.displayName = DropdownMenuPrimitive.RadioItem.displayName;

const DropdownMenuLabel = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.Label>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Label> & {
    inset?: boolean;
  }
>(({ className, inset, ...props }, ref) => (
  <DropdownMenuPrimitive.Label
    ref={ref}
    className={cn(
      'growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-font-semibold',
      inset && 'growfund-pl-8',
      className,
    )}
    {...props}
  />
));
DropdownMenuLabel.displayName = DropdownMenuPrimitive.Label.displayName;

const DropdownMenuSeparator = React.forwardRef<
  React.ComponentRef<typeof DropdownMenuPrimitive.Separator>,
  React.ComponentPropsWithoutRef<typeof DropdownMenuPrimitive.Separator>
>(({ className, ...props }, ref) => (
  <DropdownMenuPrimitive.Separator
    ref={ref}
    className={cn('growfund--mx-1 growfund-my-1 growfund-h-px growfund-bg-muted', className)}
    {...props}
  />
));
DropdownMenuSeparator.displayName = DropdownMenuPrimitive.Separator.displayName;

const DropdownMenuShortcut = ({ className, ...props }: React.HTMLAttributes<HTMLSpanElement>) => {
  return (
    <span
      className={cn('growfund-ml-auto growfund-text-xs growfund-tracking-widest growfund-opacity-60', className)}
      {...props}
    />
  );
};
DropdownMenuShortcut.displayName = 'DropdownMenuShortcut';

export {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuPortal,
    DropdownMenuRadioGroup,
    DropdownMenuRadioItem,
    DropdownMenuSeparator,
    DropdownMenuShortcut,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger
};

