import { Outlet } from 'react-router';

import ElementWrapper from '@/components/element-wrapper';
import { Container } from '@/components/layouts/container';
import { DetectRouteChangeProvider } from '@/contexts/detect-route-change-context';
import { SidebarProvider } from '@/dashboards/shared/contexts/sidebar-context';
import { UserSettingsProvider } from '@/dashboards/shared/contexts/user-settings-context';
import UserSettingsSidebar from '@/dashboards/shared/layouts/user-settings-sidebar';
import UserSettingsTopbar from '@/dashboards/shared/layouts/user-settings-topbar';
import UserSidebar from '@/dashboards/shared/user-sidebar';
import { registry } from '@/lib/registry';
import { User } from '@/utils/user';

const UserSettingsLayout = () => {
  if (User.isFundraiser() || User.isCollaborator()) {
    const FundraiserSettingsLayout = registry.get('FundraiserSettingsLayout');
    return (
      <DetectRouteChangeProvider>
        <ElementWrapper>{FundraiserSettingsLayout && <FundraiserSettingsLayout />}</ElementWrapper>
      </DetectRouteChangeProvider>
    );
  }

  return (
    <DetectRouteChangeProvider>
      <UserSettingsProvider>
        <SidebarProvider>
          <div className="growfund-w-full growfund-h-full">
            <UserSidebar />
            <div
              className="lg:growfund-ms-[var(--growfund-sidebar-width)]"
              id="user-settings-content"
            >
              <UserSettingsTopbar />

              <Container
                className="growfund-flex growfund-flex-col lg:growfund-flex-row growfund-gap-4 lg:growfund-gap-6 growfund-mt-4 lg:growfund-mt-6"
                size="sm"
              >
                <UserSettingsSidebar />

                <Outlet />
              </Container>
            </div>
          </div>
        </SidebarProvider>
      </UserSettingsProvider>
    </DetectRouteChangeProvider>
  );
};

export default UserSettingsLayout;
