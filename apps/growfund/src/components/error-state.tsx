import { __ } from '@wordpress/i18n';
import React from 'react';

import { ErrorIcon } from '@/app/icons';
import { Container } from '@/components/layouts/container';
import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const ErrorState = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, children, ...props }, ref) => {
    return (
      <Container size="sm">
        <Box
          className={cn(
            'growfund-shadow-sm growfund-border-none growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-gap-2 growfund-p-6',
            className,
          )}
          ref={ref}
          {...props}
        >
          <ErrorIcon />
          {children}
        </Box>
      </Container>
    );
  },
);

ErrorState.displayName = 'ErrorState';

const ErrorStateDescription = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement>
>(({ className, children, ...props }, ref) => {
  return (
    <div
      className={cn(
        'growfund-typo-small growfund-text-fg-secondary growfund-text-center growfund-flex growfund-flex-col growfund-items-center growfund-space-y-4',
        className,
      )}
      ref={ref}
      {...props}
    >
      {children}
      <Button
        className="growfund-mt-4"
        onClick={() => {
          window.history.back();
        }}
        variant="outline"
      >
        {__('Go Back', 'growfund')}
      </Button>
    </div>
  );
});

ErrorStateDescription.displayName = 'ErrorStateDescription';

export { ErrorState, ErrorStateDescription };
