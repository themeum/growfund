import { __ } from '@wordpress/i18n';
import { Home } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import DonorInformationMetrics from '@/dashboards/donors/features/home/components/donor-information-metrics';
import RecentDonations from '@/dashboards/donors/features/home/components/recent-donations';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';

const DonorHomePageContent = () => {
  const { setTopbar } = useDashboardLayoutContext();
  useEffect(() => {
    setTopbar({
      title: __('Home', 'growfund'),
      icon: Home,
    });
  }, [setTopbar]);
  return (
    <Container className="growfund-mt-10" size="sm">
      <div className="growfund-flex growfund-items-center growfund-justify-between">
        <h4 className="growfund-typo-h5 growfund-font-semibold growfund-text-primary">
          {__('My giving stats', 'growfund')}
        </h4>
      </div>
      <div className="growfund-mt-4 growfund-space-y-4">
        <DonorInformationMetrics />
        <RecentDonations />
      </div>
    </Container>
  );
};

export default DonorHomePageContent;
