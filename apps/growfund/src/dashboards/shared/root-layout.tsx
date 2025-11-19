import { __ } from '@wordpress/i18n';
import { Home } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Outlet } from 'react-router';

import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';
import { RootLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import Topbar from '@/dashboards/shared/topbar';
import UserSidebar from '@/dashboards/shared/user-sidebar';
import { type TopbarContent } from '@/dashboards/types/types';

const RootLayout = () => {
  const [topbar, setTopbar] = useState<TopbarContent>({
    title: __('Home', 'growfund'),
    icon: Home,
  });

  const value = useMemo(() => {
    return {
      sidebarItems: [],
      topbar,
      setTopbar,
    };
  }, [topbar, setTopbar]);

  return (
    <DetectRouteChangeProvider>
      <RootLayoutContext value={value}>
        <div className="growfund-w-full growfund-h-full">
          <UserSidebar />
          <div className="growfund-ms-[var(--growfund-sidebar-width)]">
            <Topbar />
            <Outlet />
          </div>
        </div>
      </RootLayoutContext>
    </DetectRouteChangeProvider>
  );
};

export default RootLayout;
