import { __ } from '@wordpress/i18n';
import { Outlet, useNavigate } from 'react-router';

import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { RouteConfig } from '@/config/route-config';
import { useCurrentPath } from '@/hooks/use-current-path';

const DonorDetailsTabs = () => {
  const currentPath = useCurrentPath();
  const navigate = useNavigate();

  return (
    <Tabs defaultValue="overview" value={currentPath ?? 'overview'}>
      <TabsList>
        <TabsTrigger
          value="overview"
          onClick={() => navigate(RouteConfig.DonorOverview.buildLink())}
        >
          {__('Overview', 'growfund')}
        </TabsTrigger>
        <TabsTrigger
          value="donations"
          onClick={() => navigate(RouteConfig.DonorDonations.buildLink())}
        >
          {__('Donations', 'growfund')}
        </TabsTrigger>
      </TabsList>
      <TabsContent value={currentPath ?? 'overview'}>
        <Outlet />
      </TabsContent>
    </Tabs>
  );
};

export default DonorDetailsTabs;
