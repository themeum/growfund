import * as TabsPrimitive from '@radix-ui/react-tabs';
import * as React from 'react';

import { cn } from '@/lib/utils';

const TabsRoot = TabsPrimitive.Root;

const Tabs = React.forwardRef<
  React.ComponentRef<typeof TabsPrimitive.Root>,
  React.ComponentPropsWithoutRef<typeof TabsPrimitive.Root>
>(({ className, ...props }, ref) => (
  <TabsRoot ref={ref} className={cn('growfund-w-full', className)} {...props} />
));

const TabsList = React.forwardRef<
  React.ComponentRef<typeof TabsPrimitive.List>,
  React.ComponentPropsWithoutRef<typeof TabsPrimitive.List>
>(({ className, ...props }, ref) => (
  <TabsPrimitive.List
    ref={ref}
    className={cn(
      'growfund-inline-flex growfund-h-9 growfund-items-center growfund-justify-start growfund-rounded-none growfund-bg-transparent growfund-p-0 growfund-border-b growfund-border-b-border growfund-w-full',
      className,
    )}
    {...props}
  />
));
TabsList.displayName = TabsPrimitive.List.displayName;

const TabsTrigger = React.forwardRef<
  React.ComponentRef<typeof TabsPrimitive.Trigger>,
  React.ComponentPropsWithoutRef<typeof TabsPrimitive.Trigger>
>(({ className, ...props }, ref) => (
  <TabsPrimitive.Trigger
    ref={ref}
    className={cn(
      'growfund-inline-flex growfund-items-center growfund-justify-center growfund-whitespace-nowrap growfund-text-fg-secondary growfund-relative growfund-p-1 growfund-typo-small growfund-font-medium growfund-ring-offset-background growfund-transition-all focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2 disabled:growfund-pointer-events-none disabled:growfund-opacity-50 growfund-h-9 growfund-rounded-none growfund-border-b-2 growfund-border-b-transparent growfund-bg-transparent growfund-px-4 growfund-pb-3 growfund-pt-2 growfund-shadow-none data-[state=active]:growfund-border-b-border-inverse data-[state=active]:growfund-text-fg-primary',
      className,
    )}
    {...props}
  />
));
TabsTrigger.displayName = TabsPrimitive.Trigger.displayName;

const TabsContent = React.forwardRef<
  React.ComponentRef<typeof TabsPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof TabsPrimitive.Content>
>(({ className, ...props }, ref) => (
  <TabsPrimitive.Content
    ref={ref}
    className={cn(
      'growfund-ring-offset-background focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2',
      className,
    )}
    {...props}
  />
));
TabsContent.displayName = TabsPrimitive.Content.displayName;

export { Tabs, TabsContent, TabsList, TabsTrigger };
