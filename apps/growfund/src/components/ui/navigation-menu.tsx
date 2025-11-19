import * as NavigationMenuPrimitive from '@radix-ui/react-navigation-menu';
import { cva } from 'class-variance-authority';
import { ChevronDown } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const NavigationMenu = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.Root>
>(({ className, children, ...props }, ref) => (
  <NavigationMenuPrimitive.Root
    ref={ref}
    className={cn(
      'growfund-relative growfund-z-10 growfund-flex growfund-max-w-max growfund-flex-1 growfund-items-center growfund-justify-center',
      className,
    )}
    {...props}
  >
    {children}
    <NavigationMenuViewport />
  </NavigationMenuPrimitive.Root>
));
NavigationMenu.displayName = NavigationMenuPrimitive.Root.displayName;

const NavigationMenuList = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.List>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.List>
>(({ className, ...props }, ref) => (
  <NavigationMenuPrimitive.List
    ref={ref}
    className={cn(
      'growfund-group growfund-flex growfund-flex-1 growfund-list-none growfund-items-center growfund-justify-center growfund-space-x-1',
      className,
    )}
    {...props}
  />
));
NavigationMenuList.displayName = NavigationMenuPrimitive.List.displayName;

const NavigationMenuItem = NavigationMenuPrimitive.Item;

const navigationMenuTriggerStyle = cva(
  'growfund-group growfund-inline-flex growfund-h-9 growfund-w-max growfund-items-center growfund-justify-center growfund-rounded-md growfund-bg-background growfund-px-4 growfund-py-2 growfund-typo-small growfund-font-medium growfund-transition-colors hover:growfund-bg-accent hover:growfund-text-fg-accent focus:growfund-bg-accent focus:growfund-text-fg-accent focus:growfund-outline-none disabled:growfund-pointer-events-none disabled:growfund-opacity-50 data-[active]:bg-accent/50 data-[state=open]:bg-accent/50',
);

const NavigationMenuTrigger = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.Trigger>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.Trigger>
>(({ className, children, ...props }, ref) => (
  <NavigationMenuPrimitive.Trigger
    ref={ref}
    className={cn(navigationMenuTriggerStyle(), 'group', className)}
    {...props}
  >
    {children}{' '}
    <ChevronDown
      className="growfund-relative growfund-top-[1px] growfund-ml-1 growfund-h-3 growfund-w-3 growfund-transition growfund-duration-300 group-data-[state=open]:rotate-180"
      aria-hidden="true"
    />
  </NavigationMenuPrimitive.Trigger>
));
NavigationMenuTrigger.displayName = NavigationMenuPrimitive.Trigger.displayName;

const NavigationMenuContent = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.Content>
>(({ className, ...props }, ref) => (
  <NavigationMenuPrimitive.Content
    ref={ref}
    className={cn(
      'growfund-left-0 growfund-top-0 growfund-w-full data-[motion^=from-]:growfund-animate-in data-[motion^=to-]:growfund-animate-out data-[motion^=from-]:growfund-fade-in data-[motion^=to-]:growfund-fade-out data-[motion=from-end]:growfund-slide-in-from-right-52 data-[motion=from-start]:growfund-slide-in-from-left-52 data-[motion=to-end]:growfund-slide-out-to-right-52 data-[motion=to-start]:growfund-slide-out-to-left-52 md:growfund-absolute md:growfund-w-auto',
      className,
    )}
    {...props}
  />
));
NavigationMenuContent.displayName = NavigationMenuPrimitive.Content.displayName;

const NavigationMenuLink = NavigationMenuPrimitive.Link;

const NavigationMenuViewport = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.Viewport>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.Viewport>
>(({ className, ...props }, ref) => (
  <div className={cn('growfund-absolute growfund-left-0 growfund-top-full growfund-flex growfund-justify-center')}>
    <NavigationMenuPrimitive.Viewport
      className={cn(
        'growfund-origin-top-center growfund-relative growfund-mt-1.5 growfund-h-[var(--radix-navigation-menu-viewport-height)] growfund-w-full growfund-overflow-hidden growfund-rounded-md growfund-border growfund-bg-popover growfund-text-popover-foreground growfund-shadow data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-90 md:growfund-w-[var(--radix-navigation-menu-viewport-width)]',
        className,
      )}
      ref={ref}
      {...props}
    />
  </div>
));
NavigationMenuViewport.displayName = NavigationMenuPrimitive.Viewport.displayName;

const NavigationMenuIndicator = React.forwardRef<
  React.ComponentRef<typeof NavigationMenuPrimitive.Indicator>,
  React.ComponentPropsWithoutRef<typeof NavigationMenuPrimitive.Indicator>
>(({ className, ...props }, ref) => (
  <NavigationMenuPrimitive.Indicator
    ref={ref}
    className={cn(
      'growfund-top-full growfund-z-[1] growfund-flex growfund-h-1.5 growfund-items-end growfund-justify-center growfund-overflow-hidden data-[state=visible]:growfund-animate-in data-[state=hidden]:growfund-animate-out data-[state=hidden]:growfund-fade-out data-[state=visible]:growfund-fade-in',
      className,
    )}
    {...props}
  >
    <div className="growfund-relative growfund-top-[60%] growfund-h-2 growfund-w-2 growfund-rotate-45 growfund-rounded-tl-sm growfund-bg-border growfund-shadow-md" />
  </NavigationMenuPrimitive.Indicator>
));
NavigationMenuIndicator.displayName = NavigationMenuPrimitive.Indicator.displayName;

export {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuIndicator,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
    // eslint-disable-next-line react-refresh/only-export-components
    navigationMenuTriggerStyle,
    NavigationMenuViewport
};

