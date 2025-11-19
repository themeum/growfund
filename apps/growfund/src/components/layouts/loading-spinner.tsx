import { LoaderCircle } from 'lucide-react';
import React from 'react';
import { createPortal } from 'react-dom';

import { cn } from '@/lib/utils';

const LoadingSpinner = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    return (
      <div ref={ref} className={cn(className)} {...props}>
        <LoaderCircle className="growfund-w-5 growfund-h-5 growfund-text-icon-secondary growfund-animate-spin" />
      </div>
    );
  },
);

const LoadingSpinnerOverlay = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, ...props }, ref) => {
  return createPortal(
    <div
      ref={ref}
      className={cn(
        'growfund-fixed growfund-inset-0 growfund-bg-transparent growfund-w-full growfund-h-full growfund-z-highest growfund-flex growfund-flex-1 growfund-items-center growfund-justify-center',
        className,
      )}
      {...props}
    >
      <LoaderCircle className="growfund-w-5 growfund-h-5 growfund-text-icon-secondary growfund-animate-spin" />
    </div>,
    document.getElementById('growfund-root') ?? document.body,
  );
});

LoadingSpinnerOverlay.displayName = 'LoadingSpinnerOverlay';
LoadingSpinner.displayName = 'LoadingSpinner';

export { LoadingSpinner, LoadingSpinnerOverlay };
