import React from 'react';

import { cn } from '@/lib/utils';

const Screen = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} className={cn('growfund-group/screen growfund-w-full growfund-h-full', className)} {...props}>
        {children}
      </div>
    );
  },
);

Screen.displayName = 'Screen';

const ScreenTitle = React.forwardRef<HTMLHeadingElement, React.HTMLAttributes<HTMLHeadingElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <h4
        ref={ref}
        className={cn('growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary', className)}
        {...props}
      >
        {children}
      </h4>
    );
  },
);

ScreenTitle.displayName = 'ScreenTitle';

const ScreenDescription = React.forwardRef<
  HTMLParagraphElement,
  React.HTMLAttributes<HTMLParagraphElement>
>(({ children, className, ...props }, ref) => {
  return (
    <p
      ref={ref}
      className={cn('growfund-typo-tiny growfund-font-regular growfund-text-fg-secondary', className)}
      {...props}
    >
      {children}
    </p>
  );
});

ScreenDescription.displayName = 'ScreenDescription';

const ScreenContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} className={cn('growfund-size-full', className)} {...props}>
        {children}
      </div>
    );
  },
);

ScreenContent.displayName = 'ScreenContent';

const ScreenFooter = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-w-full growfund-flex growfund-items-center growfund-justify-end growfund-gap-6 growfund-absolute growfund-right-8 growfund-bottom-8',
          className,
        )}
        {...props}
      >
        {children}
      </div>
    );
  },
);

ScreenFooter.displayName = 'ScreenFooter';

export { Screen, ScreenContent, ScreenDescription, ScreenFooter, ScreenTitle };
