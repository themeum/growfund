import { __ } from '@wordpress/i18n';
import { MoreHorizontal } from 'lucide-react';
import * as React from 'react';

import { Button, type ButtonProps } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const Pagination = ({ className, ...props }: React.ComponentProps<'nav'>) => (
  <nav
    role="navigation"
    aria-label="pagination"
    className={cn('growfund-mx-auto growfund-flex growfund-w-full growfund-justify-center', className)}
    {...props}
  />
);
Pagination.displayName = 'Pagination';

const PaginationContent = React.forwardRef<HTMLUListElement, React.ComponentProps<'ul'>>(
  ({ className, ...props }, ref) => (
    <ul
      ref={ref}
      className={cn('growfund-flex growfund-flex-row growfund-items-center growfund-gap-1', className)}
      {...props}
    />
  ),
);
PaginationContent.displayName = 'PaginationContent';

const PaginationItem = React.forwardRef<HTMLLIElement, React.ComponentProps<'li'>>(
  ({ className, ...props }, ref) => <li ref={ref} className={cn('', className)} {...props} />,
);
PaginationItem.displayName = 'PaginationItem';

type PaginationLinkProps = {
  isActive?: boolean;
  disabled?: boolean;
  onClick?: () => void;
} & Pick<ButtonProps, 'size'> &
  React.ComponentProps<'a'>;

const PaginationLink = ({
  className,
  isActive,
  disabled,
  onClick,
  size = 'icon',
  ...props
}: PaginationLinkProps) => (
  <Button
    aria-current={isActive ? 'page' : undefined}
    type="button"
    variant="outline"
    size={size}
    className={cn(
      isActive && 'growfund-bg-background-fill-brand growfund-text-fg-light growfund-typo-tiny',
      className,
    )}
    disabled={disabled}
    onClick={onClick}
  >
    {props.children}
  </Button>
);
PaginationLink.displayName = 'PaginationLink';

const PaginationPrevious = ({
  className,
  disabled,
  ...props
}: React.ComponentProps<typeof PaginationLink> & { disabled?: boolean }) => (
  <PaginationLink
    aria-label="Go to previous page"
    size="default"
    className={cn('growfund-gap-1 growfund-typo-tiny', className)}
    disabled={disabled}
    {...props}
  >
    <span>{__('Prev', 'growfund')}</span>
  </PaginationLink>
);
PaginationPrevious.displayName = 'PaginationPrevious';

const PaginationNext = ({ className, ...props }: React.ComponentProps<typeof PaginationLink>) => (
  <PaginationLink
    aria-label="Go to next page"
    size="default"
    className={cn('growfund-gap-1 growfund-typo-tiny', className)}
    {...props}
  >
    <span>{__('Next', 'growfund')}</span>
  </PaginationLink>
);
PaginationNext.displayName = 'PaginationNext';

const PaginationEllipsis = ({ className, ...props }: React.ComponentProps<'span'>) => (
  <span
    aria-hidden
    className={cn(
      'growfund-flex growfund-h-9 growfund-w-9 growfund-items-center growfund-justify-center growfund-bg-background-white growfund-border growfund-border-border growfund-rounded-md',
      className,
    )}
    {...props}
  >
    <MoreHorizontal className="growfund-h-4 growfund-w-4" />
  </span>
);
PaginationEllipsis.displayName = 'PaginationEllipsis';

export {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious
};

