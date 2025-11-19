import { __ } from '@wordpress/i18n';
import { HeartHandshake } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import BackerPledgesPageContent from '@/dashboards/backers/components/backer-pledges-page-content';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import { User as CurrentUser } from '@/utils/user';

const UserPledgesPage = () => {
  const { setTopbar } = useDashboardLayoutContext();

  useEffect(() => {
    setTopbar({
      title: __('Pledges', 'growfund'),
      icon: HeartHandshake,
    });
  }, [setTopbar]);

  if (!CurrentUser.isBacker()) {
    return <div>{__('You are not allowed to access this page.', 'growfund')}</div>;
  }

  return (
    <Container className="growfund-mt-10" size="sm">
      <BackerPledgesPageContent />
    </Container>
  );
};

export default UserPledgesPage;
