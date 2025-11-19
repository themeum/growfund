import * as React from 'react';

import { cn } from '@/lib/utils';

const Textarea = React.forwardRef<HTMLTextAreaElement, React.ComponentProps<'textarea'>>(
  ({ className, ...props }, ref) => {
    return (
      <textarea
        className={cn(
          'growfund-flex growfund-min-h-[3.75rem] growfund-w-full growfund-rounded-md growfund-border growfund-border-border growfund-bg-transparent growfund-px-3 growfund-py-2 growfund-typo-small growfund-text-fg-primary placeholder:growfund-text-fg-subdued placeholder:growfund-font-regular growfund-ring-offset-2 focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring disabled:growfund-cursor-not-allowed disabled:growfund-opacity-50',
          className,
        )}
        ref={ref}
        {...props}
      />
    );
  },
);
Textarea.displayName = 'Textarea';

export { Textarea };
