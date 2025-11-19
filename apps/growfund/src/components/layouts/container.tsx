import { cva, type VariantProps } from 'class-variance-authority';
import React from 'react';

import { cn } from '@/lib/utils';

const containerVariants = cva('growfund-w-full growfund-mx-auto growfund-h-full growfund-px-4 @5xl/main:growfund-px-0', {
  variants: {
    size: {
      default: 'growfund-max-w-[var(--growfund-container-width)]',
      lg: 'growfund-max-w-[var(--growfund-container-width-lg)]',
      sm: 'growfund-max-w-[var(--growfund-container-width-sm)]',
      xs: 'growfund-max-w-[var(--growfund-container-width-xs)]',
    },
  },
  defaultVariants: {
    size: 'default',
  },
});

type ContainerProps = React.HTMLAttributes<HTMLDivElement> & VariantProps<typeof containerVariants>;

const Container = React.forwardRef<HTMLDivElement, ContainerProps>(
  ({ children, size, className, ...props }, ref) => {
    return (
      <div className="growfund-@container/main growfund-w-full growfund-h-full">
        <div className={cn(containerVariants({ size, className }))} ref={ref} {...props}>
          {children}
        </div>
      </div>
    );
  },
);

Container.displayName = 'Container';
export { Container };
