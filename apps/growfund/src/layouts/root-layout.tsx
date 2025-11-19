import { Outlet } from 'react-router';

import FeatureGuard from '@/components/feature-guard';
import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';
import { registry } from '@/lib/registry';
import { User } from '@/utils/user';

const RootLayout = () => {
  if (User.isFundraiser()) {
    const FundraiserRootLayout = registry.get('FundraiserRootLayout');
    return (
      <DetectRouteChangeProvider>
        <FeatureGuard feature="fundraisers">
          {FundraiserRootLayout && <FundraiserRootLayout />}
        </FeatureGuard>
      </DetectRouteChangeProvider>
    );
  }

  return (
    <DetectRouteChangeProvider>
      <div className="growfund-w-full growfund-h-full">
        <Outlet />
      </div>
    </DetectRouteChangeProvider>
  );
};

export default RootLayout;
