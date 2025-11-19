import * as DialogPrimitive from '@radix-ui/react-dialog';
import { X } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const Dialog = DialogPrimitive.Root;

const DialogTrigger = DialogPrimitive.Trigger;

const DialogPortal = DialogPrimitive.Portal;

const DialogClose = DialogPrimitive.Close;

const DialogOverlay = React.forwardRef<
  React.ComponentRef<typeof DialogPrimitive.Overlay>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Overlay>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Overlay
    ref={ref}
    className={cn(
      'growfund-fixed growfund-inset-0 growfund-z-overlay growfund-bg-background-dark/80 data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0',
      className,
    )}
    {...props}
  />
));
DialogOverlay.displayName = DialogPrimitive.Overlay.displayName;

const DialogContent = React.forwardRef<
  React.ComponentRef<typeof DialogPrimitive.Content>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Content>
>(({ className, children, ...props }, ref) => {
  const [isWPMediaOpen, setIsWPMediaOpen] = React.useState(false);

  React.useEffect(() => {
    const handle = (event: Event) => {
      const detail = (event as CustomEvent<{ isOpen: boolean }>).detail;
      setIsWPMediaOpen(detail.isOpen);
    };
    window.addEventListener('wp-media-open', handle);

    return () => {
      window.removeEventListener('wp-media-open', handle);
    };
  });

  return (
    <DialogPortal container={document.getElementById('growfund-root')}>
      {!isWPMediaOpen && <DialogOverlay className="growfund-bg-background-dark/80 growfund-backdrop-blur-sm" />}

      <DialogPrimitive.Content
        ref={ref}
        aria-describedby=""
        className={cn(
          'growfund-fixed growfund-left-[50%] growfund-top-[50%] growfund-grid growfund-w-full growfund-max-w-lg growfund-translate-x-[-50%] growfund-translate-y-[-50%] growfund-gap-4 growfund-border growfund-bg-background-secondary growfund-overflow-hidden growfund-z-dialog growfund-shadow-lg growfund-duration-200 data-[state=open]:growfund-animate-in data-[state=closed]:growfund-animate-out data-[state=closed]:growfund-fade-out-0 data-[state=open]:growfund-fade-in-0 data-[state=closed]:growfund-zoom-out-95 data-[state=open]:growfund-zoom-in-95 data-[state=closed]:growfund-slide-out-to-left-1/2 data-[state=closed]:growfund-slide-out-to-top-[48%] data-[state=open]:growfund-slide-in-from-left-1/2 data-[state=open]:growfund-slide-in-from-top-[48%] sm:growfund-rounded-lg',
          className,
        )}
        {...props}
      >
        {children}
      </DialogPrimitive.Content>
    </DialogPortal>
  );
});
DialogContent.displayName = DialogPrimitive.Content.displayName;

const DialogCloseButton = React.forwardRef<
  React.ComponentRef<typeof DialogPrimitive.Close>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Close>
>(({ className, ...props }, ref) => {
  return (
    <DialogPrimitive.Close
      ref={ref}
      className={cn(
        'growfund-absolute growfund-right-4 growfund-top-3 growfund-rounded-sm growfund-opacity-70 growfund-size-8 growfund-flex growfund-items-center growfund-justify-center growfund-ring-offset-background !growfund-bg-background-fill-secondary growfund-transition-opacity hover:growfund-opacity-100 focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2 disabled:growfund-pointer-events-none data-[state=open]:growfund-bg-accent data-[state=open]:growfund-text-fg-muted',
        className,
      )}
      {...props}
    >
      <X className="growfund-size-4" />
      <span className="growfund-sr-only">Close</span>
    </DialogPrimitive.Close>
  );
});
DialogCloseButton.displayName = 'DialogCloseButton';

const DialogHeader = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div
    className={cn(
      'growfund-flex growfund-py-3 growfund-px-4 growfund-text-center growfund-min-h-[3.75rem] growfund-items-center growfund-bg-background-white growfund-border-b growfund-border-b-border sm:growfund-text-left',
      className,
    )}
    {...props}
  />
);
DialogHeader.displayName = 'DialogHeader';

const DialogFooter = ({ className, ...props }: React.HTMLAttributes<HTMLDivElement>) => (
  <div
    className={cn(
      'growfund-flex growfund-flex-col-reverse growfund-bg-background-white sm:growfund-flex-row sm:growfund-justify-end sm:growfund-space-x-2 growfund-px-4 growfund-py-3 growfund-border-t growfund-border-t-border growfund-relative growfund-z-highest',
      className,
    )}
    {...props}
  />
);
DialogFooter.displayName = 'DialogFooter';

const DialogTitle = React.forwardRef<
  React.ComponentRef<typeof DialogPrimitive.Title>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Title>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Title
    ref={ref}
    className={cn(
      'growfund-typo-h6 growfund-font-medium growfund-leading-none growfund-tracking-tight growfund-text-fg-primary',
      className,
    )}
    {...props}
  />
));
DialogTitle.displayName = DialogPrimitive.Title.displayName;

const DialogDescription = React.forwardRef<
  React.ComponentRef<typeof DialogPrimitive.Description>,
  React.ComponentPropsWithoutRef<typeof DialogPrimitive.Description>
>(({ className, ...props }, ref) => (
  <DialogPrimitive.Description
    ref={ref}
    className={cn('growfund-typo-small growfund-text-fg-muted', className)}
    {...props}
  />
));
DialogDescription.displayName = DialogPrimitive.Description.displayName;

export {
    Dialog,
    DialogClose,
    DialogCloseButton,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogOverlay,
    DialogPortal,
    DialogTitle,
    DialogTrigger
};

