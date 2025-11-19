import { __ } from '@wordpress/i18n';
import { Settings } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';

const DonorSettingsPage = () => {
  const { setTopbar } = useDashboardLayoutContext();
  useEffect(() => {
    setTopbar({
      title: __('Settings', 'growfund'),
      icon: Settings,
    });
  }, [setTopbar]);
  return (
    <Container className="growfund-mt-10">
      <h1 className="growfund-typo-h1">{__('Settings', 'growfund')}</h1>
    </Container>
  );
};

export default DonorSettingsPage;
