import { Slot } from '@radix-ui/react-slot';
import { cva, type VariantProps } from 'class-variance-authority';
import { Loader2 } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';

const buttonVariants = cva(
  'growfund-inline-flex growfund-items-center growfund-justify-center growfund-gap-2 growfund-typo-small growfund-font-medium  growfund-whitespace-nowrap growfund-rounded-md growfund-transition-colors focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2 disabled:growfund-pointer-events-none disabled:growfund-opacity-50 [&_svg]:growfund-pointer-events-none [&_svg]:growfund-size-4 [&_svg]:growfund-shrink-0 growfund-relative',
  {
    variants: {
      variant: {
        primary:
          'growfund-bg-background-fill-brand-var growfund-text-fg-light-var growfund-border-border hover:growfund-bg-background-fill-brand-hover-var',
        destructive:
          'growfund-bg-background-fill-critical growfund-text-fg-light hover:growfund-bg-background-fill-critical/90',
        'primary-soft': 'growfund-bg-background-fill-success-secondary growfund-text-fg-success',
        'destructive-soft': 'growfund-bg-background-fill-critical-secondary growfund-text-fg-critical',
        outline:
          'growfund-border growfund-border-border growfund-bg-background-fill hover:growfund-bg-background-fill-hover hover:growfund-text-fg-primary',
        secondary:
          'growfund-bg-background-fill-secondary growfund-text-fg-primary hover:growfund-bg-background-fill-secondary-hover',
        ghost: 'growfund-text-fg-primary hover:growfund-bg-background-fill-hover',
        link: 'growfund-text-fg-primary growfund-underline-offset-4 hover:growfund-underline',
      },
      size: {
        default: 'growfund-h-9 growfund-px-4 growfund-py-2',
        sm: 'growfund-h-8 growfund-rounded-md growfund-px-3 growfund-typo-tiny',
        lg: 'growfund-h-10 growfund-rounded-md growfund-px-8',
        icon: 'growfund-h-9 growfund-w-9',
      },
    },
    defaultVariants: {
      variant: 'primary',
      size: 'default',
    },
  },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>['variant'];

export interface ButtonProps
  extends React.ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  asChild?: boolean;
  type?: 'button' | 'submit' | 'reset';
  loading?: boolean;
}

const Button = React.forwardRef<HTMLButtonElement, ButtonProps>(
  (
    {
      className,
      variant,
      size,
      type = 'button',
      asChild = false,
      children,
      loading = false,
      ...props
    },
    ref,
  ) => {
    const Comp = asChild ? Slot : 'button';
    return (
      <Comp
        className={cn(buttonVariants({ variant, size, className }))}
        ref={ref}
        type={type}
        {...props}
      >
        {loading && (
          <span className="growfund-absolute growfund-left-[50%] growfund-top-[50%] growfund-translate-x-[-50%] growfund-translate-y-[-50%] growfund-w-full growfund-h-full growfund-backdrop-blur-2xl growfund-flex growfund-items-center growfund-justify-center growfund-rounded-md">
            <Loader2 className="growfund-size-4 growfund-animate-spin" />
          </span>
        )}
        {children}
      </Comp>
    );
  },
);
Button.displayName = 'Button';

// eslint-disable-next-line react-refresh/only-export-components
export { Button, buttonVariants };
