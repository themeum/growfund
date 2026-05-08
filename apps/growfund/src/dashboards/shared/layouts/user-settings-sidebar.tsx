import { __ } from '@wordpress/i18n';
import { Banknote, Bell, UserCircle } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import { useUserSettingsContext } from '@/dashboards/shared/contexts/user-settings-context';
import SidebarItem from '@/dashboards/shared/sidebar-item';
import { createAcronym } from '@/utils';
import { User as CurrentUser } from '@/utils/user';

const UserSettingsSidebar = () => {
  const { user } = useUserSettingsContext();

  if (!CurrentUser.isBacker() && !CurrentUser.isDonor() && !CurrentUser.isFundraiser()) {
    return null;
  }

  const getUserRoleTitle = (user: typeof CurrentUser) => {
    if (user.isBacker()) {
      return __('Backer', 'growfund');
    }

    if (user.isDonor()) {
      return __('Donor', 'growfund');
    }

    if (user.isFundraiser()) {
      return __('Fundraiser', 'growfund');
    }

    return null;
  };

  return (
    <div className="growfund-max-w-[13.25rem] growfund-w-full growfund-bg-background-surface growfund-border growfund-border-border growfund-rounded-md growfund-shadow-sm growfund-sticky growfund-top-[72px]  growfund-overflow-hidden growfund-h-fit">
      <div className="growfund-p-4 growfund-flex growfund-items-center growfund-gap-2 growfund-border-b growfund-border-b-muted growfund-bg-background-surface-alt">
        <Avatar>
          <AvatarImage src={user?.image?.url ?? undefined} />
          <AvatarFallback>
            {createAcronym({ first_name: user?.first_name, last_name: user?.last_name })}
          </AvatarFallback>
        </Avatar>
        <div>
          <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
            {user?.display_name}
          </p>
          <p className="growfund-typo-tiny growfund-text-fg-secondary">
            {getUserRoleTitle(CurrentUser)}
          </p>
        </div>
      </div>
      <div className="growfund-p-3 growfund-space-y-2">
        <SidebarItem
          item={{
            label: __('Account', 'growfund'),
            route: UserRouteConfig.AccountSettings,
            icon: UserCircle,
            child_routes: [UserRouteConfig.AccountSettings.template],
          }}
        />
        <SidebarItem
          item={{
            label: __('Payouts', 'growfund-pro'),
            route: UserRouteConfig.PayoutSettings,
            icon: Banknote,
            child_routes: [UserRouteConfig.PayoutSettings.template],
          }}
        />
        <SidebarItem
          item={{
            label: __('Notifications', 'growfund'),
            route: UserRouteConfig.NotificationsSettings,
            icon: Bell,
            child_routes: [UserRouteConfig.NotificationsSettings.template],
          }}
        />
      </div>
    </div>
  );
};

export default UserSettingsSidebar;
