import { __ } from '@wordpress/i18n';
import { Home } from 'lucide-react';
import { useEffect } from 'react';

import { Container } from '@/components/layouts/container';
import BackerGivingStats from '@/dashboards/backers/features/overview/components/backer-giving-stats';
import BackerRecentPledges from '@/dashboards/backers/features/overview/components/backer-recent-pledges';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import useCurrentUser from '@/hooks/use-current-user';

const BackerHomePageContent = () => {
  const { setTopbar } = useDashboardLayoutContext();
  const { currentUser } = useCurrentUser();

  useEffect(() => {
    setTopbar({
      title: __('Home', 'growfund'),
      icon: Home,
    });
  }, [setTopbar]);

  return (
    <Container className="growfund-mt-10 growfund-space-y-4" size="sm">
      <BackerGivingStats backerId={currentUser.id} />
      <BackerRecentPledges />
    </Container>
  );
};

export default BackerHomePageContent;
