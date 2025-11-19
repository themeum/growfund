import { __ } from '@wordpress/i18n';

import { MetricsCard } from '@/features/overview/components/ui/metrics-card';
import { useCurrency } from '@/hooks/use-currency';

const DonorMetricsFallback = () => {
  const { toCurrencyCompact } = useCurrency();
  return (
    <div className="growfund-space-y-4 growfund-mt-4">
      <div className="growfund-grid growfund-gap-4">
        <div className="growfund-flex growfund-items-center growfund-gap-4">
          <MetricsCard
            data={{
              label: __('Total Donated', 'growfund'),
              amount: toCurrencyCompact(721000),
            }}
            isPro
          />
          <MetricsCard
            data={{
              label: __('Average Donation', 'growfund'),
              amount: toCurrencyCompact(300000),
            }}
            isPro
          />
          <MetricsCard
            data={{
              label: __('Donated Campaigns', 'growfund'),
              amount: '20',
            }}
            isPro
          />
          <MetricsCard
            data={{
              label: __('Total Donation', 'growfund'),
              amount: '1156',
            }}
            isPro
          />
        </div>
      </div>
    </div>
  );
};

export default DonorMetricsFallback;
