import { __ } from '@wordpress/i18n';
import {
  CreditCard,
  FileHeart,
  Home,
  KeyRound,
  Lock,
  type LucideIcon,
  Mail,
  Paintbrush,
  Receipt,
  Settings2,
  UserCog,
} from 'lucide-react';
import React from 'react';
import { Link } from 'react-router';

import { WhiteLabelIcon } from '@/app/icons';
import { growfundConfig } from '@/config/growfund';
import { RouteConfig } from '@/config/route-config';
import { useCurrentPath } from '@/hooks/use-current-path';
import { cn } from '@/lib/utils';
import { User } from '@/utils/user';

interface SidebarMenu {
  label: string;
  icon: LucideIcon;
  route: string;
}

const sidebarMenus: SidebarMenu[] = [
  {
    label: __('General', 'growfund'),
    icon: Home,
    route: RouteConfig.GeneralSettings.buildLink(),
  },
  {
    label: __('Campaign', 'growfund'),
    icon: FileHeart,
    route: RouteConfig.CampaignSettings.buildLink(),
  },
  {
    label: __('User & Permissions', 'growfund'),
    icon: UserCog,
    route: RouteConfig.UserAndPermissionsSettings.buildLink(),
  },
  {
    label: __('Payment', 'growfund'),
    icon: CreditCard,
    route: RouteConfig.PaymentSettings.buildLink(),
  },
  {
    label: __('PDF Receipt', 'growfund'),
    icon: Receipt,
    route: RouteConfig.PdfReceiptSettings.buildLink(),
  },
  {
    label: __('Email & Notifications', 'growfund'),
    icon: Mail,
    route: RouteConfig.EmailAndNotificationsSettings.buildLink(),
  },
  {
    label: __('Security', 'growfund'),
    icon: Lock,
    route: RouteConfig.SecuritySettings.buildLink(),
  },
  {
    label: __('Branding', 'growfund'),
    icon: Paintbrush,
    route: RouteConfig.BrandingSettings.buildLink(),
  },
  {
    label: __('Advanced', 'growfund'),
    icon: Settings2,
    route: RouteConfig.AdvancedSettings.buildLink(),
  },
  {
    label: __('License', 'growfund'),
    icon: KeyRound,
    route: RouteConfig.LicenseSettings.buildLink(),
  },
];

const SettingsSidebar = React.forwardRef<HTMLDivElement, React.HTMLAttributes<HTMLDivElement>>(
  ({ className, ...props }, ref) => {
    const currentPath = useCurrentPath();

    return (
      <div
        ref={ref}
        className={cn(
          'growfund-w-full growfund-h-full growfund-bg-background-surface growfund-shadow-sm growfund-border growfund-border-border growfund-overflow-hidden growfund-rounded-lg growfund-sticky growfund-top-[calc(var(--growfund-topbar-height)_+_var(--growfund-wp-topbar-height)_+_1.5rem)]',
          User.isFundraiser() && 'growfund-top-[calc(var(--growfund-topbar-height)_+_1.5rem)]',
          className,
        )}
        {...props}
      >
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-p-4 growfund-border-b growfund-bg-background-surface-alt growfund-border-border-muted">
          <div>
            <WhiteLabelIcon className="growfund-rounded-md" />
          </div>
          <div className="growfund-grid">
            <span className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
              {__('Growfund', 'growfund')}
            </span>
            <a
              href="https://growfund.com"
              target="_blank"
              rel="noopener noreferrer"
              className="growfund-typo-tiny growfund-text-fg-muted growfund-truncate hover:growfund-underline hover:growfund-text-fg-emphasis"
              title="growfund.com"
            >
              growfund.com
            </a>
          </div>
        </div>
        <ul className="growfund-flex growfund-flex-col growfund-py-4 growfund-px-3">
          {sidebarMenus.map(({ label, icon: Icon, route }, index) => {
            const isActive = route === currentPath;

            if (
              route === RouteConfig.LicenseSettings.template &&
              !growfundConfig.has_growfund_pro
            ) {
              return null;
            }

            return (
              <li
                key={index}
                className={cn(
                  'growfund-group/sidebar-menu growfund-relative growfund-typo-small growfund-text-fg-primary growfund-h-8 growfund-flex growfund-items-center growfund-px-3 growfund-rounded-lg',
                  'hover:growfund-bg-background-fill-secondary hover:growfund-text-fg-primary',
                  isActive &&
                    'growfund-bg-background-fill-success-secondary hover:growfund-bg-background-fill-success-secondary',
                )}
              >
                <Link
                  to={route}
                  className={cn(
                    'growfund-flex growfund-items-center growfund-gap-2 growfund-h-full growfund-w-full',
                    'hover:growfund-text-[inherit]',
                    isActive && 'growfund-text-fg-brand growfund-font-medium',
                  )}
                >
                  <Icon
                    className={cn(
                      'growfund-w-4 growfund-h-4 growfund-text-icon-primary group-hover/sidebar-menu:growfund-text-icon-primary-hover',
                      isActive && 'growfund-text-icon-brand',
                    )}
                  />
                  <span>{label}</span>
                </Link>
                {isActive && (
                  <span className="growfund-w-1 growfund-h-6 growfund-bg-background-fill-brand growfund-rounded-full growfund-absolute growfund-left-0 growfund-top-1/2 growfund-translate-y-[-50%]" />
                )}
              </li>
            );
          })}
        </ul>
      </div>
    );
  },
);

export default SettingsSidebar;
