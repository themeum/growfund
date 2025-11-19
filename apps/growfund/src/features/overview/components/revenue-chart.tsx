import { __ } from '@wordpress/i18n';

import LineChart from '@/components/charts/line-chart';
import { LoadingSkeleton } from '@/components/layouts/loading-skeleton';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { InfoTooltip } from '@/components/ui/tooltip';
import { type RevenueChart } from '@/features/analytics/schemas/analytics';
import { useCurrency } from '@/hooks/use-currency';
import { shortenNumber } from '@/utils/currency';

const RevenueChart = ({
  data,
  loading = false,
  className,
}: {
  data: RevenueChart[];
  loading?: boolean;
  className?: string;
}) => {
  const { toCurrencyCompact } = useCurrency();
  return (
    <Card className={className}>
      <CardHeader>
        <CardTitle>
          {__('Revenue for Period', 'growfund')}
          <InfoTooltip>
            {__('Revenue for Period is the total revenue for the period.', 'growfund')}
          </InfoTooltip>
        </CardTitle>
      </CardHeader>
      <CardContent className="growfund-w-full growfund-h-[18.75rem]">
        <LoadingSkeleton
          loading={loading}
          className="growfund-rounded-md growfund-w-full"
          skeletonClassName="growfund-rounded-md growfund-w-full growfund-min-h-64"
        >
          <LineChart
            data={data.map((item) => ({ xData: item.date, yData: item.revenue }))}
            tooltipFormatter={(value) => [
              toCurrencyCompact(value as number),
              __('Revenue', 'growfund'),
            ]}
            yAxisFormatter={(value) => shortenNumber(value)}
          />
        </LoadingSkeleton>
      </CardContent>
    </Card>
  );
};

export default RevenueChart;
