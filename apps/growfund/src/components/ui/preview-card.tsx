import React from 'react';

import { cn } from '@/lib/utils';

import { Image } from './image';

interface PreviewCardProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'title'> {
  title: React.ReactNode | string;
  subtitle?: React.ReactNode | string;
  image?: string | null;
  alt?: string;
  action?: React.ReactNode;
}

const PreviewCard = React.forwardRef<HTMLDivElement, PreviewCardProps>(
  ({ className, title, subtitle, image, alt, action, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className={cn(
          'growfund-grid growfund-grid-cols-[3.5rem_auto] growfund-gap-3 growfund-bg-background-surface growfund-border-border-secondary growfund-rounded-md growfund-p-2 growfund-shadow-sm growfund-group/preview-card',
          className,
        )}
        {...props}
      >
        <Image
          src={image}
          alt={alt}
          className="growfund-rounded-md growfund-flex-shrink-0"
          fit="cover"
          aspectRatio="square"
        />
        <div className="growfund-flex growfund-items-center growfund-justify-between growfund-gap-2">
          <div className="growfund-flex growfund-flex-col growfund-gap-2">
            <h3 className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{title}</h3>
            {subtitle && <div className="growfund-typo-tiny growfund-text-fg-secondary">{subtitle}</div>}
          </div>
          {action && (
            <div className="growfund-flex growfund-items-end growfund-justify-end growfund-opacity-0 group-hover/preview-card:growfund-opacity-100 growfund-transition-opacity">
              {action}
            </div>
          )}
        </div>
      </div>
    );
  },
);

export default PreviewCard;
