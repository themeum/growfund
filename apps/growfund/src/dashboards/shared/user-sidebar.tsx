import { __ } from '@wordpress/i18n';
import { Bookmark, HeartHandshake, HelpingHand, Home, Receipt, User } from 'lucide-react';

import { useAppConfig } from '@/contexts/app-config';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import Sidebar from '@/dashboards/shared/sidebar';
import { type SidebarItem } from '@/dashboards/types/types';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type AppConfig } from '@/features/settings/schemas/settings';
import { isDefined } from '@/utils';
import { User as CurrentUser } from '@/utils/user';

const backerSidebarItems: SidebarItem[] = [
  {
    label: __('Home', 'growfund'),
    icon: Home,
    route: UserRouteConfig.Home,
    child_routes: [],
  },
  {
    label: __('Pledges', 'growfund'),
    icon: HeartHandshake,
    route: UserRouteConfig.Pledges,
    child_routes: [],
  },
  {
    label: __('Backed Campaigns', 'growfund'),
    icon: HelpingHand,
    route: UserRouteConfig.BackedCampaigns,
    child_routes: [],
  },
  {
    label: __('Bookmarks', 'growfund'),
    icon: Bookmark,
    route: UserRouteConfig.Bookmarks,
    child_routes: [],
  },
  {
    label: __('Profile', 'growfund'),
    icon: User,
    route: UserRouteConfig.Profile,
    child_routes: [],
  },
];
const donorSidebarItems: SidebarItem[] = [
  {
    label: __('Home', 'growfund'),
    icon: Home,
    route: UserRouteConfig.Home,
    child_routes: [],
  },
  {
    label: __('Donations', 'growfund'),
    icon: HeartHandshake,
    route: UserRouteConfig.Donations,
    child_routes: [],
  },
  {
    label: __('Annual Receipt', 'growfund'),
    icon: Receipt,
    route: UserRouteConfig.AnnualReceipts,
    child_routes: [],
  },
  {
    label: __('Bookmarks', 'growfund'),
    icon: Bookmark,
    route: UserRouteConfig.Bookmarks,
    child_routes: [],
  },
  {
    label: __('Profile', 'growfund'),
    icon: User,
    route: UserRouteConfig.Profile,
    child_routes: [],
  },
];

const getSidebarItems = (provider: 'backer' | 'donor' | null, appConfig: AppConfig) => {
  if (!isDefined(provider)) {
    return [];
  }

  const items = new Map<'backer' | 'donor', SidebarItem[]>([
    ['backer', backerSidebarItems],
    [
      'donor',
      donorSidebarItems.filter((item) => {
        if (item.route.template === UserRouteConfig.AnnualReceipts.template) {
          return !!appConfig[AppConfigKeys.Receipt]?.enable_annual_receipt;
        }
        return true;
      }),
    ],
  ]);
  return items.get(provider) ?? [];
};

const UserSidebar = () => {
  const { appConfig } = useAppConfig();
  const getSidebarProvider = () => {
    if (!CurrentUser.isBacker() && !CurrentUser.isDonor()) {
      return null;
    }
    return CurrentUser.isBacker() ? 'backer' : 'donor';
  };

  return <Sidebar items={getSidebarItems(getSidebarProvider(), appConfig)} />;
};

export default UserSidebar;
