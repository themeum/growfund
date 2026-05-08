import { GearIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import {
  Bookmark,
  CreditCard,
  FileHeart,
  FileText,
  HeartHandshake,
  HelpingHand,
  Home,
  Receipt,
  Undo2,
  User,
  Users2,
} from 'lucide-react';
import { Link, useLocation } from 'react-router';

import { BrandIcon } from '@/app/icons';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { Separator } from '@/components/ui/separator';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import ProfileMenu from '@/dashboards/shared/components/dropdowns/profile-menu';
import SidebarItem from '@/dashboards/shared/sidebar-item';
import { type SidebarItem as SidebarItemType } from '@/dashboards/types/types';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import { User as CurrentUser } from '@/utils/user';

const sidebarItems: SidebarItemType[] = [
  {
    label: __('Home', 'growfund'),
    icon: Home,
    route: RouteConfig.Home,
    child_routes: [],
  },
  {
    label: __('My Campaigns', 'growfund'),
    icon: FileHeart,
    route: RouteConfig.Campaigns,
    child_routes: [
      RouteConfig.CampaignStepBasic.template,
      RouteConfig.CampaignStepGoal.template,
      RouteConfig.CampaignStepRewards.template,
      RouteConfig.CampaignStepAdditional.template,
      RouteConfig.CampaignSettings.template,
    ],
  },
  {
    label: __('Pledges', 'growfund'),
    icon: HeartHandshake,
    route: RouteConfig.Pledges,
    child_routes: [],
  },
  {
    label: __('Backers', 'growfund'),
    icon: Users2,
    route: RouteConfig.Backers,
    child_routes: [],
  },
  {
    label: __('Analytics', 'growfund'),
    icon: FileText,
    route: RouteConfig.Analytics,
    child_routes: [],
  },
  {
    label: __('Wallet', 'growfund-pro'),
    icon: CreditCard,
    route: RouteConfig.FundraiserWallet,
    child_routes: [],
    hidden: CurrentUser.isCollaborator(),
  },
  {
    label: __('Profile', 'growfund'),
    icon: User,
    route: RouteConfig.FundraiserProfile,
    child_routes: [],
  },
];

const sidebarItemsDonationsMode: SidebarItem[] = [
  {
    label: __('Home', 'growfund'),
    icon: Home,
    route: RouteConfig.Home,
    child_routes: [],
  },
  {
    label: __('My Campaigns', 'growfund'),
    icon: FileHeart,
    route: RouteConfig.Campaigns,
    child_routes: [
      RouteConfig.CampaignStepBasic.template,
      RouteConfig.CampaignStepGoal.template,
      RouteConfig.CampaignStepRewards.template,
      RouteConfig.CampaignStepAdditional.template,
      RouteConfig.CampaignSettings.template,
    ],
  },
  {
    label: __('Donations', 'growfund'),
    icon: HeartHandshake,
    route: RouteConfig.Donations,
    child_routes: [],
  },
  {
    label: __('Donors', 'growfund'),
    icon: Users2,
    route: RouteConfig.Donors,
    child_routes: [],
  },
  {
    label: __('Analytics', 'growfund'),
    icon: FileText,
    route: RouteConfig.Analytics,
    child_routes: [],
  },
  {
    label: __('Wallet', 'growfund-pro'),
    icon: CreditCard,
    route: RouteConfig.FundraiserWallet,
    child_routes: [],
    hidden: CurrentUser.isCollaborator(),
  },
  {
    label: __('Profile', 'growfund'),
    icon: User,
    route: RouteConfig.FundraiserProfile,
    child_routes: [],
  },
];

const pledgeSidebarItems: SidebarItem[] = [
  {
    label: __('My pledges', 'growfund'),
    icon: HelpingHand,
    route: RouteConfig.FundraiserMyPledges,
    child_routes: [],
  },
  {
    label: __('Bookmarks', 'growfund'),
    icon: Bookmark,
    route: RouteConfig.FundraiserBookmarks,
    child_routes: [],
  },
];

const donationSidebarItems: SidebarItem[] = [
  {
    label: __('My donations', 'growfund'),
    icon: HelpingHand,
    route: RouteConfig.FundraiserMyDonations,
    child_routes: [],
  },
  {
    label: __('Annual Receipt', 'growfund'),
    icon: Receipt,
    route: RouteConfig.FundraiserAnnualReceipts,
    child_routes: [],
  },
  {
    label: __('Bookmarks', 'growfund'),
    icon: Bookmark,
    route: RouteConfig.FundraiserBookmarks,
    child_routes: [],
  },
];

const FundraiserSidebar = () => {
  const { pathname } = useLocation();
  const { isDonationMode, appConfig } = useAppConfig();
  const items = isDonationMode ? sidebarItemsDonationsMode : sidebarItems;

  const contributionItems = isDonationMode
    ? donationSidebarItems.filter((item) => {
        if (item.route.template === RouteConfig.FundraiserAnnualReceipts.template) {
          return !!appConfig[AppConfigKeys.Receipt]?.enable_annual_receipt;
        }
        return true;
      })
    : pledgeSidebarItems;

  const brandLogo = appConfig[AppConfigKeys.Branding]?.logo?.url;
  const brandLogoHeight = appConfig[AppConfigKeys.Branding]?.logo_height ?? 28;

  return (
    <div
      id="fundraiser-sidebar"
      className="growfund-fixed growfund-h-full growfund-w-[var(--growfund-sidebar-width)] growfund-bg-background-surface-alt growfund-border-r growfund-border-r-border growfund-overflow-hidden"
    >
      {/* topbar */}
      <div className="growfund-h-[var(--growfund-topbar-height)] growfund-flex growfund-items-center growfund-px-4 growfund-border-b growfund-border-b-border growfund-group/sidebar-logo growfund-relative">
        <div
          className="growfund-flex growfund-items-center growfund-gap-2 growfund-absolute growfund-left-4 growfund-transition-all growfund-duration-300 group-hover/sidebar-logo:growfund-left-[12.5rem] group-hover/sidebar-logo:growfund-opacity-0"
          style={{ '--growfund-brand-logo-height': `${brandLogoHeight}px` } as React.CSSProperties}
        >
          {brandLogo ? (
            <Image
              src={brandLogo}
              alt="Brand Logo"
              className={cn(
                'growfund-h-7 growfund-border-none growfund-bg-transparent',
                'growfund-h-[var(--growfund-brand-logo-height)]',
              )}
              fit="contain"
            />
          ) : (
            <BrandIcon className="growfund-h-5" />
          )}
        </div>
        <Button
          variant="secondary"
          className="growfund-absolute growfund-opacity-0 growfund-transition-all growfund-left-[-12.5rem] group-hover/sidebar-logo:growfund-left-4 group-hover/sidebar-logo:growfund-opacity-100"
          onClick={() => {
            window.location.href = '/';
          }}
        >
          <Undo2 />
          {__('Back to site', 'growfund')}
        </Button>
      </div>

      {/* sidebar items */}
      <div className="growfund-flex growfund-flex-col growfund-gap-2 growfund-h-full">
        <div className="growfund-flex growfund-flex-col growfund-justify-between growfund-h-full">
          <div className="growfund-space-y-4 growfund-overflow-auto growfund-flex-1">
            <div className="growfund-py-4 growfund-flex growfund-flex-col growfund-gap-2">
              {items
                .filter((item) => !isDefined(item.hidden) || !item.hidden)
                .map((item, index) => {
                  return <SidebarItem key={index} item={item} />;
                })}

              <div className="growfund-px-3">
                <Link
                  to={RouteConfig.FundraiserSettings.buildLink()}
                  className={cn(
                    'growfund-w-full growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-font-medium growfund-text-fg-secondary growfund-min-h-8 growfund-px-3 growfund-py-2 growfund-rounded-lg growfund-relative growfund-group/sidebar-item',
                    pathname.startsWith(RouteConfig.FundraiserSettings.template)
                      ? 'growfund-bg-background-fill-success-var growfund-text-fg-success-var [&>svg]:growfund-text-icon-success-var'
                      : 'hover:growfund-bg-background-secondary hover:growfund-text-fg-secondary',
                  )}
                >
                  <GearIcon
                    className={cn(
                      'growfund-size-4 growfund-text-icon-primary',
                      !pathname.startsWith(RouteConfig.FundraiserSettings.template) &&
                        'group-hover/sidebar-item:growfund-text-fg-secondary',
                    )}
                  />
                  <span>{__('Settings', 'growfund')}</span>
                  {pathname.startsWith(RouteConfig.FundraiserSettings.template) && (
                    <span className="growfund-absolute growfund-w-1 growfund-h-6 growfund-left-0 growfund-top-1/2 growfund--translate-y-1/2 growfund-rounded-lg growfund-bg-background-fill-brand-var" />
                  )}
                </Link>
              </div>
            </div>

            <div>
              <Separator />
              <div className="growfund-py-6">
                <div className="growfund-px-6 growfund-pb-3 growfund-typo-tiny growfund-text-fg-subdued">
                  {isDonationMode
                    ? __('Donor options', 'growfund')
                    : __('Backer options', 'growfund')}
                </div>
                <div className="growfund-space-y-2">
                  {contributionItems.map((item, index) => {
                    return <SidebarItem key={index} item={item} />;
                  })}
                </div>
              </div>
            </div>
          </div>

          <div className="growfund-w-full growfund-min-h-32  growfund-p-4 growfund-border-t growfund-border-t-border growfund-bg-background-surface">
            <ProfileMenu className="growfund-px-2" />
          </div>
        </div>
      </div>
    </div>
  );
};

export default FundraiserSidebar;
