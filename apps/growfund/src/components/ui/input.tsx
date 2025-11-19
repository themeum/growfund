import { Search } from 'lucide-react';
import * as React from 'react';

import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const Input = React.forwardRef<
  HTMLInputElement,
  React.ComponentProps<'input'> & {
    autoFocusVisible?: boolean;
    prefixText?: string;
    postfixText?: string;
    rootClassName?: string;
  }
>(
  (
    { className, rootClassName, type, autoFocusVisible = false, prefixText, postfixText, ...props },
    ref,
  ) => {
    React.useEffect(() => {
      if (autoFocusVisible && ref && typeof ref !== 'function') {
        ref.current?.focus();
      }
    }, [autoFocusVisible, ref]);

    return (
      <div className={cn('growfund-relative growfund-w-full', rootClassName)}>
        <input
          type={type}
          className={cn(
            'growfund-flex growfund-min-h-9 growfund-w-full growfund-rounded-md growfund-ring-offset-2 growfund-border growfund-border-border growfund-px-3 growfund-typo-small growfund-transition-colors growfund-bg-background-surface placeholder:growfund-text-fg-subdued disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50',
            'file:growfund-border-0 file:growfund-leading-[1.7] file:growfund-bg-transparent file:growfund-font-bold file:growfund-text-fg-primary',
            'focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring',
            type === 'search' && 'growfund-ps-8',
            isDefined(prefixText) && 'growfund-ps-8',
            type === 'search' && isDefined(prefixText) && 'growfund-ps-12',
            isDefined(postfixText) && 'growfund-pe-3',
            className,
          )}
          ref={ref}
          {...props}
        />
        {type === 'search' && (
          <Search className="growfund-w-4 growfund-h-4 growfund-text-icon-secondary growfund-absolute growfund-top-[50%] growfund-left-3 growfund-translate-y-[-50%]" />
        )}
        {prefixText && (
          <div
            className={cn(
              'growfund-absolute growfund-text-subdued growfund-left-3 growfund-top-[50%] growfund-translate-y-[-50%]',
              type === 'search' && 'growfund-left-8',
            )}
          >
            {prefixText}
          </div>
        )}
        {postfixText && (
          <div className=" growfund-absolute growfund-text-subdued growfund-right-2 growfund-top-[50%] growfund-translate-y-[-50%]">
            {postfixText}
          </div>
        )}
      </div>
    );
  },
);
Input.displayName = 'Input';

export { Input };
