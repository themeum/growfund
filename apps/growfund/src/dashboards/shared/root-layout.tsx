import { __ } from '@wordpress/i18n';
import { Home, Menu } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Outlet } from 'react-router';

import { BrandIcon } from '@/app/icons';
import { Button } from '@/components/ui/button';
import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';
import { RootLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';
import { SidebarProvider, useSidebarContext } from '@/dashboards/shared/contexts/sidebar-context';
import Topbar from '@/dashboards/shared/topbar';
import UserSidebar from '@/dashboards/shared/user-sidebar';
import { type TopbarContent } from '@/dashboards/types/types';
import { cn } from '@/lib/utils';

const RootLayout = () => {
  return (
    <SidebarProvider>
      <RootLayoutInner />
    </SidebarProvider>
  );
};

const RootLayoutInner = () => {
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
  const { isOpenSidebar, setOpenSidebar } = useSidebarContext();

  return (
    <DetectRouteChangeProvider>
      <RootLayoutContext value={value}>
        <div className="growfund-w-full growfund-h-full">
          <div
            className={cn(
              'lg:growfund-hidden growfund-flex growfund-items-center growfund-justify-between growfund-px-4 growfund-h-[var(--growfund-topbar-height)] growfund-border-b growfund-border-b-border growfund-bg-background-surface-alt',
              isOpenSidebar && 'growfund-hidden',
            )}
          >
            <Button
              variant="ghost"
              onClick={() => {
                setOpenSidebar(true);
              }}
              aria-label={__('Open menu', 'growfund')}
            >
              <Menu />
            </Button>
            <BrandIcon className="growfund-h-5 growfund-ml-2" />
            <div className="growfund-w-8" />
          </div>

          {isOpenSidebar && (
            <div
              className="lg:growfund-hidden growfund-fixed growfund-inset-0 growfund-bg-black/40 growfund-z-40"
              onClick={() => {
                setOpenSidebar(false);
              }}
            />
          )}
          <UserSidebar />
          <div className="lg:growfund-ms-[var(--growfund-sidebar-width)]">
            <Topbar />
            <Outlet />
          </div>
        </div>
      </RootLayoutContext>
    </DetectRouteChangeProvider>
  );
};

export default RootLayout;
