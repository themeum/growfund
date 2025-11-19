import * as SheetPrimitive from '@radix-ui/react-dialog';
import { cva, type VariantProps } from 'class-variance-authority';
import { X } from 'lucide-react';
import * as React from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const Sheet = SheetPrimitive.Root;

const SheetTrigger = SheetPrimitive.Trigger;

const SheetClose = SheetPrimitive.Close;

const SheetPortal = SheetPrimitive.Portal;

const SheetOverlay = React.forwardRef<
  React.ComponentRef<typeof SheetPrimitive.Overlay>,
  React.ComponentPropsWithoutRef<typeof SheetPrimitive.Overlay>
>(({ className, ...props }, ref) => (
  <SheetPrimitive.Overlay
    className={cn(
      'growfund-fixed growfund-inset-0 growfund-z-overlay growfund-bg-black/80 growfund-backdrop-blur-sm data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0',
      className,
    )}
    {...props}
    ref={ref}
  />
));
SheetOverlay.displayName = SheetPrimitive.Overlay.displayName;

const sheetVariants = cva(
  'growfund-fixed growfund-z-dialog growfund-overflow-hidden growfund-bg-background-surface-secondary growfund-shadow-lg growfund-rounded-tl-xl growfund-rounded-tr-xl growfund-mx-auto growfund-transition growfund-ease-in-out data-[state=closed]:growfund-duration-300 data-[state=open]:growfund-duration-500 data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out',
  {
    variants: {
      side: {
        top: 'growfund-inset-x-0 growfund-top-0 growfund-border-b data-[state=closed]:growfund-slide-out-to-top data-[state=open]:growfund-slide-in-from-top',
        bottom:
          'growfund-inset-x-0 growfund-bottom-0 growfund-border-t data-[state=closed]:growfund-slide-out-to-bottom data-[state=open]:growfund-slide-in-from-bottom',
        left: 'growfund-inset-y-0 growfund-left-0 growfund-h-full growfund-w-3/4 growfund-border-r data-[state=closed]:growfund-slide-out-to-left data-[state=open]:growfund-slide-in-from-left sm:growfund-max-w-sm',
        right:
          'growfund-inset-y-0 growfund-right-0 growfund-h-full growfund-w-3/4 growfund-border-l data-[state=closed]:growfund-slide-out-to-right data-[state=open]:growfund-slide-in-from-right sm:growfund-max-w-sm',
      },
      size: {
        regular: 'growfund-max-w-[var(--growfund-container-width-lg)]',
        md: 'growfund-max-w-[var(--growfund-container-width)]',
        sm: 'growfund-max-w-[var(--growfund-container-width-sm)]',
        xs: 'growfund-max-w-[var(--growfund-container-width-xs)]',
      },
    },
    defaultVariants: {
      side: 'right',
      size: 'regular',
    },
  },
);

interface SheetContentProps
  extends React.ComponentPropsWithoutRef<typeof SheetPrimitive.Content>,
    VariantProps<typeof sheetVariants> {}

const SheetContent = React.forwardRef<
  React.ComponentRef<typeof SheetPrimitive.Content>,
  SheetContentProps
>(({ side = 'right', className, children, ...props }, ref) => (
  <SheetPortal container={document.getElementById('growfund-root')}>
    <SheetOverlay />
    <SheetPrimitive.Content ref={ref} className={cn(sheetVariants({ side }), className)} {...props}>
      <SheetPrimitive.Close asChild>
        <Button
          variant="secondary"
          size="icon"
          className="growfund-absolute growfund-z-popover growfund-right-6 growfund-top-3"
        >
          <X className="growfund-h-4 growfund-w-4" />
          <span className="growfund-sr-only">Close</span>
        </Button>
      </SheetPrimitive.Close>
      {children}
    </SheetPrimitive.Content>
  </SheetPortal>
));
SheetContent.displayName = SheetPrimitive.Content.displayName;

const SheetHeader = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div
    className={cn(
      'growfund-flex growfund-flex-col growfund-space-y-2 growfund-text-center sm:growfund-text-left growfund-border-b growfund-border-b-border growfund-min-h-[3.75rem] growfund-h-fit growfund-px-6 growfund-py-4 growfund-bg-background-surface',
      className,
    )}
    {...props}
  />
);
SheetHeader.displayName = 'SheetHeader';

const SheetFooter = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div
    className={cn(
      'growfund-flex growfund-flex-col-reverse sm:growfund-flex-row sm:growfund-justify-end sm:growfund-space-x-2',
      className,
    )}
    {...props}
  />
);
SheetFooter.displayName = 'SheetFooter';

const SheetTitle = React.forwardRef<
  React.ComponentRef<typeof SheetPrimitive.Title>,
  React.ComponentPropsWithoutRef<typeof SheetPrimitive.Title>
>(({ className, ...props }, ref) => (
  <SheetPrimitive.Title
    ref={ref}
    className={cn(
      'growfund-flex growfund-items-center growfund-gap-3 growfund-typo-h6 growfund-text-fg-primary growfund-font-semibold [&>svg]:growfund-size-6 [&>svg]:growfund-text-icon-primary',
      className,
    )}
    {...props}
  />
));
SheetTitle.displayName = SheetPrimitive.Title.displayName;

const SheetDescription = React.forwardRef<
  React.ComponentRef<typeof SheetPrimitive.Description>,
  React.ComponentPropsWithoutRef<typeof SheetPrimitive.Description>
>(({ className, ...props }, ref) => (
  <SheetPrimitive.Description
    ref={ref}
    className={cn('growfund-typo-small growfund-text-muted-foreground', className)}
    {...props}
  />
));
SheetDescription.displayName = SheetPrimitive.Description.displayName;

export {
    Sheet,
    SheetClose,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetOverlay,
    SheetPortal,
    SheetTitle,
    SheetTrigger
};

