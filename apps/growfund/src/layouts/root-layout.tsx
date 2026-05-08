import { Outlet } from 'react-router';

import ElementWrapper from '@/components/element-wrapper';
import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';
import { registry } from '@/lib/registry';
import { User } from '@/utils/user';

const RootLayout = () => {
  if (User.isFundraiser() || User.isCollaborator()) {
    const FundraiserRootLayout = registry.get('FundraiserRootLayout');
    return (
      <DetectRouteChangeProvider>
        <ElementWrapper>{FundraiserRootLayout && <FundraiserRootLayout />}</ElementWrapper>
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
