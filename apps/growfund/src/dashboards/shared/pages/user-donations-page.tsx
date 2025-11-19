import { __ } from '@wordpress/i18n';
import { HeartHandshake } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import DonationPageContent from '@/dashboards/donors/features/donations/components/donation-page-content';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';

const UserDonationsPage = () => {
  const { setTopbar } = useDashboardLayoutContext();

  useEffect(() => {
    setTopbar({
      title: __('Donations', 'growfund'),
      icon: HeartHandshake,
    });
  }, [setTopbar]);

  return (
    <Container className="growfund-mt-10 growfund-space-y-3" size="sm">
      <DonationPageContent />
    </Container>
  );
};

export default UserDonationsPage;
