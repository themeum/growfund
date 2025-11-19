import { Outlet } from 'react-router';

import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';

const PublicRootLayout = () => {
  return (
    <DetectRouteChangeProvider>
      <div className="growfund-w-full growfund-h-full">
        <Outlet />
      </div>
    </DetectRouteChangeProvider>
  );
};

export default PublicRootLayout;
