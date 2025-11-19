import { __ } from '@wordpress/i18n';
import { ArrowLeft } from 'lucide-react';
import React from 'react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface PageTitleProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'title'> {
  title: string | React.ReactNode;
  onGoBack?: () => void;
  icon?: React.ReactNode;
}

const PageTitle = React.forwardRef<HTMLDivElement, PageTitleProps>(
  ({ title, onGoBack, icon, className, ...props }, ref) => {
    return (
      <div
        className={cn('growfund-h-full growfund-flex growfund-items-center growfund-gap-2', className)}
        ref={ref}
        {...props}
      >
        {isDefined(onGoBack) && (
          <div className="growfund-group/back growfund-peer/back">
            <Button
              variant="ghost"
              size="sm"
              onClick={onGoBack}
              className="hover:growfund-bg-background-white growfund-px-0 group-hover/back:growfund-px-3 growfund-transition-all"
            >
              <ArrowLeft className="growfund-text-icon-primary" />
              <span className="growfund-opacity-0 growfund-w-0 growfund-px-0 group-hover/back:growfund-opacity-100 group-hover/back:growfund-w-auto growfund-transition-opacity">
                {__('Exit', 'growfund')}
              </span>
            </Button>
          </div>
        )}
        <div className="growfund-flex growfund-items-center growfund-gap-3 peer-hover/back:growfund-opacity-0 growfund-transition-opacity">
          {isDefined(icon) && icon}
          {isDefined(title) && (
            <h6
              className={cn(
                'growfund-typo-h6 growfund-font-medium growfund-text-fg-primary',
                typeof title === 'string' && 'growfund-line-clamp-2 growfund-break-all',
              )}
            >
              {title}
            </h6>
          )}
        </div>
      </div>
    );
  },
);

export default PageTitle;
