import { cva, type VariantProps } from 'class-variance-authority';
import { MinusIcon, TrendingDown, TrendingUp } from 'lucide-react';
import React from 'react';

import { ProBadge } from '@/components/ui/pro-badge';
import { InfoTooltip } from '@/components/ui/tooltip';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const metricsCardVariants = cva(
  'growfund-p-6 growfund-rounded-md growfund-w-full growfund-flex growfund-flex-col growfund-gap-2 growfund-group/metric-card',
  {
    variants: {
      variant: {
        default: 'growfund-bg-background-fill',
        primary: 'growfund-bg-[#E3F5FF]',
        secondary: 'growfund-bg-background-fill-secondary',
        success: 'growfund-bg-[#DFF4E9]',
        warning: 'growfund-bg-[#FFF4C2]',
        critical: 'growfund-bg-[#FFEBE2]',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

interface MetricsCardProps
  extends React.HTMLAttributes<HTMLDivElement>,
    VariantProps<typeof metricsCardVariants> {
  data: {
    label: string;
    amount: string;
    growth?: number;
    tooltip?: string | React.ReactNode;
  };
  isPro?: boolean;
}

const GrowthIcon = ({ growth }: { growth: number }) => {
  if (growth === 0) {
    return <MinusIcon className="growfund-text-icon-secondary growfund-size-6" />;
  }
  if (growth > 0) {
    return <TrendingUp className="growfund-text-icon-success growfund-size-6" />;
  }
  return <TrendingDown className="growfund-text-icon-critical growfund-size-6" />;
};

const MetricsCard = React.forwardRef<HTMLDivElement, MetricsCardProps>(
  ({ className, variant, data, isPro = false, ...props }, ref) => {
    return (
      <div ref={ref} className={cn(metricsCardVariants({ variant }), className)} {...props}>
        <p
          className="growfund-typo-small growfund-font-medium growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-2 [&>[data-type=tooltip]]:growfund-opacity-0 group-hover/metric-card:[&>[data-type=tooltip]]:growfund-opacity-100 growfund-transition-opacity"
          title={data.label}
        >
          <span className="growfund-truncate">
            {data.label} {isPro && <ProBadge />}
          </span>
          {data.tooltip && <InfoTooltip>{data.tooltip}</InfoTooltip>}
        </p>
        <div className="growfund-flex growfund-items-center growfund-gap-2">
          <div
            className={cn(
              'growfund-typo-h3 growfund-font-medium growfund-text-fg-primary',
              isPro &&
                'growfund-bg-gradient-to-b growfund-from-black growfund-to-white growfund-bg-clip-text growfund-text-transparent growfund-opacity-10',
            )}
          >
            {data.amount}
          </div>

          {isDefined(data.growth) && (
            <div
              className={cn(
                'growfund-typo-tiny growfund-text-fg-secondary growfund-flex growfund-items-center growfund-gap-2',
                isPro && 'growfund-opacity-20',
              )}
            >
              <span>
                {data.growth > 0 ? '+' : ''}
                {data.growth}%
              </span>
              <GrowthIcon growth={data.growth} />
            </div>
          )}
        </div>
      </div>
    );
  },
);

MetricsCard.displayName = 'MetricsCard';
type MetricsCardVariants = VariantProps<typeof metricsCardVariants>;

export { MetricsCard, type MetricsCardVariants };
