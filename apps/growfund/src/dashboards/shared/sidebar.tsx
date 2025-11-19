import { __ } from '@wordpress/i18n';
import { Settings, Undo2 } from 'lucide-react';
import { Link, useLocation } from 'react-router';

import { BrandIcon } from '@/app/icons';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { useAppConfig } from '@/contexts/app-config';
import ProfileMenu from '@/dashboards/shared/components/dropdowns/profile-menu';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import SidebarItem from '@/dashboards/shared/sidebar-item';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { cn } from '@/lib/utils';

const Sidebar = ({ items }: { items: SidebarItem[] }) => {
  const { pathname } = useLocation();
  const { appConfig } = useAppConfig();

  const brandLogo = appConfig[AppConfigKeys.Branding]?.logo?.url;

  return (
    <div className="growfund-fixed growfund-h-full growfund-w-[var(--growfund-sidebar-width)] growfund-bg-background-surface-alt growfund-border-r growfund-border-r-border growfund-overflow-hidden">
      {/* topbar */}
      <div className="growfund-h-[var(--growfund-topbar-height)] growfund-flex growfund-items-center growfund-px-4 growfund-border-b growfund-border-b-border growfund-group/sidebar-logo growfund-relative">
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-absolute growfund-left-4 growfund-transition-all growfund-duration-300 group-hover/sidebar-logo:growfund-left-[12.5rem] group-hover/sidebar-logo:growfund-opacity-0">
          {brandLogo ? (
            <Image
              src={brandLogo}
              alt="Brand Logo"
              className="growfund-h-7 growfund-border-none growfund-bg-transparent"
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
              {items.map((item, index) => {
                return <SidebarItem key={index} item={item} />;
              })}
              <div className="growfund-px-3">
                <Link
                  to={UserRouteConfig.Settings.buildLink()}
                  className={cn(
                    'growfund-w-full growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-font-medium growfund-text-fg-secondary growfund-min-h-8 growfund-px-3 growfund-py-2 growfund-rounded-lg growfund-relative growfund-group/sidebar-item hover:growfund-bg-background-secondary hover:growfund-text-fg-secondary',
                    pathname.startsWith(UserRouteConfig.Settings.template) &&
                      'growfund-bg-background-fill-success-var growfund-text-fg-success-var [&>svg]:growfund-text-icon-success-var',
                  )}
                >
                  <Settings className="growfund-size-4 growfund-text-icon-primary group-hover/sidebar-item:growfund-text-fg-secondary" />
                  <span>{__('Settings', 'growfund')}</span>
                  {pathname.startsWith(UserRouteConfig.Settings.template) && (
                    <span className="growfund-absolute growfund-w-1 growfund-h-6 growfund-left-0 growfund-top-1/2 growfund--translate-y-1/2 growfund-rounded-lg growfund-bg-background-fill-brand" />
                  )}
                </Link>
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

export default Sidebar;
