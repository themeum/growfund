import { type DialogProps } from '@radix-ui/react-dialog';
import { Command as CommandPrimitive } from 'cmdk';
import { Search } from 'lucide-react';
import * as React from 'react';

import { Dialog, DialogContent } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

const Command = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive>
>(({ className, ...props }, ref) => (
  <CommandPrimitive
    ref={ref}
    className={cn(
      'growfund-flex growfund-h-full growfund-w-full growfund-flex-col growfund-overflow-hidden growfund-rounded-md growfund-bg-background-surface growfund-text-fg-primary',
      className,
    )}
    {...props}
  />
));
Command.displayName = CommandPrimitive.displayName;

const CommandDialog = ({ children, ...props }: DialogProps) => {
  return (
    <Dialog {...props}>
      <DialogContent className="growfund-overflow-hidden growfund-p-0">
        <Command className="[&_[cmdk-group-heading]]:growfund-px-2 [&_[cmdk-group-heading]]:growfund-font-medium [&_[cmdk-group-heading]]:growfund-text-fg-muted [&_[cmdk-group]:growfund-not([hidden])_~[cmdk-group]]:growfund-pt-0 [&_[cmdk-group]]:growfund-px-2 [&_[cmdk-input-wrapper]_svg]:growfund-h-5 [&_[cmdk-input-wrapper]_svg]:growfund-w-5 [&_[cmdk-input]]:growfund-h-12 [&_[cmdk-item]]:growfund-px-2 [&_[cmdk-item]]:growfund-py-3 [&_[cmdk-item]_svg]:growfund-h-5 [&_[cmdk-item]_svg]:growfund-w-5">
          {children}
        </Command>
      </DialogContent>
    </Dialog>
  );
};

const CommandInput = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.Input>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.Input> & {
    showSearchIcon?: boolean;
    wrapperClassName?: string;
  }
>(({ className, showSearchIcon = true, wrapperClassName, ...props }, ref) => (
  <div className={cn('growfund-flex growfund-items-center growfund-px-3', wrapperClassName)} cmdk-input-wrapper="">
    {showSearchIcon && <Search className="growfund-mr-2 growfund-h-4 growfund-w-4 growfund-shrink-0 growfund-opacity-50" />}
    <CommandPrimitive.Input
      ref={ref}
      className={cn(
        'growfund-flex growfund-h-9 growfund-w-full growfund-rounded-md growfund-bg-transparent growfund-border-0 growfund-px-0 focus:growfund-shadow-none focus-visible:growfund-shadow-none growfund-py-3 growfund-typo-small growfund-outline-none placeholder:growfund-text-fg-secondary disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50',
        className,
      )}
      {...props}
    />
  </div>
));

CommandInput.displayName = CommandPrimitive.Input.displayName;

const CommandList = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.List>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.List>
>(({ className, ...props }, ref) => (
  <div className="growfund-max-h-[18.75rem] growfund-flex growfund-flex-1 growfund-flex-col growfund-overflow-y-auto" ref={ref}>
    <CommandPrimitive.List className={cn('', className)} {...props} />
  </div>
));

CommandList.displayName = CommandPrimitive.List.displayName;

const CommandEmpty = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.Empty>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.Empty>
>((props, ref) => (
  <CommandPrimitive.Empty ref={ref} className="growfund-py-6 growfund-text-center growfund-typo-small" {...props} />
));

CommandEmpty.displayName = CommandPrimitive.Empty.displayName;

const CommandGroup = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.Group>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.Group>
>(({ className, ...props }, ref) => (
  <CommandPrimitive.Group
    ref={ref}
    className={cn(
      'growfund-overflow-y-auto growfund-p-1 growfund-text-fg-secondary [&_[cmdk-group-heading]]:growfund-px-2 [&_[cmdk-group-heading]]:growfund-py-1.5 [&_[cmdk-group-heading]]:growfund-text-xs [&_[cmdk-group-heading]]:growfund-font-medium [&_[cmdk-group-heading]]:growfund-text-fg-secondary',
      className,
    )}
    {...props}
  />
));

CommandGroup.displayName = CommandPrimitive.Group.displayName;

const CommandSeparator = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.Separator>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.Separator>
>(({ className, ...props }, ref) => (
  <CommandPrimitive.Separator
    ref={ref}
    className={cn('-growfund-mx-1 growfund-h-px growfund-bg-border', className)}
    {...props}
  />
));
CommandSeparator.displayName = CommandPrimitive.Separator.displayName;

const CommandItem = React.forwardRef<
  React.ComponentRef<typeof CommandPrimitive.Item>,
  React.ComponentPropsWithoutRef<typeof CommandPrimitive.Item>
>(({ className, ...props }, ref) => (
  <CommandPrimitive.Item
    ref={ref}
    className={cn(
      'growfund-relative growfund-flex growfund-cursor-default growfund-gap-2 growfund-select-none growfund-items-center growfund-rounded-sm growfund-px-2 growfund-py-1.5 growfund-typo-small growfund-text-fg-primary growfund-outline-none data-[disabled=true]:growfund-pointer-events-none data-[selected=true]:growfund-bg-accent data-[selected=true]:growfund-text-fg-accent data-[disabled=true]:growfund-opacity-50 [&_svg]:growfund-pointer-events-none [&_svg]:growfund-size-4 [&_svg]:growfund-shrink-0',
      className,
    )}
    {...props}
  />
));

CommandItem.displayName = CommandPrimitive.Item.displayName;

const CommandShortcut = ({ className, ...props }: React.HTMLAttributes<HTMLSpanElement>) => {
  return (
    <span
      className={cn('growfund-ml-auto growfund-typo-tiny growfund-tracking-widest growfund-text-fg-secondary', className)}
      {...props}
    />
  );
};
CommandShortcut.displayName = 'CommandShortcut';

export {
    Command,
    CommandDialog,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
    CommandShortcut
};

