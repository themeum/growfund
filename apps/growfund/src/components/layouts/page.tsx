import { cva, type VariantProps } from 'class-variance-authority';
import React, { Suspense } from 'react';

import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import PageTitle from '@/components/layouts/page-title';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import { User } from '@/utils/user';

const Page = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <Suspense fallback={<LoadingSpinnerOverlay />}>
        <div
          ref={ref}
          className={cn('growfund-mb-16 growfund-@container/page', className)}
          {...props}
        >
          {children}
        </div>
      </Suspense>
    );
  },
);

const headerVariants = cva(
  'growfund-max-w-[var(--growfund-container-width)] growfund-w-full growfund-h-full growfund-flex growfund-items-center growfund-border-box growfund-px-4 @5xl/page:growfund-px-0',
  {
    variants: {
      variant: {
        default: 'growfund-mx-auto',
        fluid: 'growfund-max-w-full growfund-px-8 @5xl/page:growfund-px-8',
      },
      size: {
        default:
          'growfund-min-h-[var(--growfund-topbar-height)] growfund-max-h-[var(--growfund-topbar-height)]',
        lg: 'growfund-min-h-[calc(var(--growfund-topbar-height)_+_12px)] growfund-max-h-[calc(var(--growfund-topbar-height)_+_12px)]',
        sm: 'growfund-min-h-[var(--growfund-topbar-height)] growfund-max-w-[var(--growfund-container-width-sm)]',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'default',
    },
  },
);

interface PageHeaderProps
  extends React.HTMLAttributes<HTMLDivElement>, VariantProps<typeof headerVariants> {
  name?: string | React.ReactNode;
  icon?: React.ReactNode;
  action?: React.ReactNode;
  onGoBack?: () => void;
  children?: React.ReactNode;
}

const PageHeader = React.forwardRef<HTMLDivElement, PageHeaderProps>(
  ({ children, className, name, icon, action, variant, size, onGoBack, ...props }, ref) => {
    return (
      <div
        ref={ref}
        data-page-header-container
        className={cn(
          'growfund-w-full growfund-sticky growfund-z-header growfund-border-b growfund-border-b-border growfund-bg-background-surface-secondary growfund-max-h-[var(--growfund-topbar-height)] growfund-min-h-[var(--growfund-topbar-height)]',
          variant === 'fluid' ||
            [User.isFundraiser(), User.isCollaborator(), User.isBacker()].includes(true)
            ? 'growfund-top-0'
            : 'growfund-top-[var(--growfund-wp-topbar-height)]',
          className,
        )}
        {...props}
      >
        <div className={cn(headerVariants({ variant, size }))}>
          <div className="growfund-w-full growfund-flex growfund-items-center growfund-h-full growfund-justify-between">
            <PageTitle title={name} onGoBack={onGoBack} icon={icon} />
            {isDefined(children) && children}
            {isDefined(action) && action}
          </div>
        </div>
      </div>
    );
  },
);

const PageSubHeader = React.forwardRef<
  HTMLDivElement,
  React.HTMLAttributes<HTMLDivElement> & {
    title: string | React.ReactNode;
    action?: React.ReactNode;
  }
>(({ className, title, action, ...props }, ref) => {
  return (
    <div
      ref={ref}
      className={cn('growfund-flex growfund-items-center growfund-justify-between', className)}
      {...props}
    >
      <h4 className="growfund-typo-h4 growfund-text-fg-primary/80">{title}</h4>
      {isDefined(action) && action}
    </div>
  );
});

const PageContent = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ children, className, ...props }, ref) => {
    return (
      <div ref={ref} className={cn('growfund-w-full', className)} {...props}>
        {children}
      </div>
    );
  },
);

Page.displayName = 'Page';
PageHeader.displayName = 'PageHeader';
PageContent.displayName = 'PageContent';
PageSubHeader.displayName = 'PageSubHeader';

export { Page, PageContent, PageHeader, PageSubHeader };
