import { GearIcon, PersonIcon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { ChevronRight, LogOut, Undo2 } from 'lucide-react';
import { Link } from 'react-router';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { RouteConfig } from '@/config/route-config';
import { Role } from '@/constants/role';
import { UserRouteConfig } from '@/dashboards/shared/config/user-route-config';
import { useLogoutMutation } from '@/dashboards/shared/services/user';
import useCurrentUser from '@/hooks/use-current-user';
import { cn } from '@/lib/utils';
import { createAcronym } from '@/utils';

const roleMap = new Map<Role, string>([
  [Role.ADMIN, __('Admin', 'growfund')],
  [Role.FUNDRAISER, __('Fundraiser', 'growfund')],
  [Role.DONOR, __('Donor', 'growfund')],
  [Role.BACKER, __('Backer', 'growfund')],
]);

const ProfileMenu = ({ className }: { className?: string }) => {
  const { currentUser: user, isFundraiser } = useCurrentUser();

  const logoutMutation = useLogoutMutation();

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <div
          className={cn(
            'growfund-flex growfund-items-center growfund-gap-2 hover:growfund-bg-background-fill-secondary-hover growfund-px-4 growfund-py-1 growfund-rounded-lg growfund-cursor-pointer [&[data-state=open]]:growfund-bg-background-fill-secondary-hover',
            className,
          )}
        >
          <Avatar className="growfund-size-8">
            <AvatarImage src={user.image?.url ?? undefined} />
            <AvatarFallback className="growfund-bg-background-fill-tertiary/50 growfund-typo-tiny">
              {createAcronym({ first_name: user.first_name, last_name: user.last_name })}
            </AvatarFallback>
          </Avatar>

          <div className="growfund-flex growfund-items-center growfund-justify-between growfund-gap-3 growfund-w-full">
            <div className="growfund-flex growfund-flex-col growfund-items-start">
              <span
                className="growfund-typo-small growfund-font-medium growfund-text-fg-secondary growfund-block growfund-truncate growfund-max-w-28"
                title={sprintf('%s %s', user.first_name, user.last_name)}
              >
                {sprintf('%s %s', user.first_name, user.last_name)}
              </span>
              <span className="growfund-typo-tiny growfund-text-fg-secondary">
                {roleMap.has(user.active_role) && roleMap.get(user.active_role)}
              </span>
            </div>
            <ChevronRight className="growfund-size-4 growfund-shrink-0 growfund-text-icon-secondary" />
          </div>
        </div>
      </DropdownMenuTrigger>
      <DropdownMenuContent
        className="growfund-w-[var(--radix-dropdown-menu-trigger-width)]"
        align="end"
        side="right"
      >
        <DropdownMenuGroup>
          <DropdownMenuItem asChild>
            <Link
              to={
                isFundraiser
                  ? RouteConfig.FundraiserProfile.buildLink()
                  : UserRouteConfig.Profile.buildLink()
              }
            >
              <PersonIcon />
              {__('Profile', 'growfund')}
            </Link>
          </DropdownMenuItem>
          <DropdownMenuItem onClick={() => (window.location.href = '/')}>
            <Undo2 />
            {__('Back to site', 'growfund')}
          </DropdownMenuItem>
          <DropdownMenuItem asChild>
            <Link
              to={
                isFundraiser
                  ? RouteConfig.FundraiserSettings.buildLink()
                  : UserRouteConfig.Settings.buildLink()
              }
            >
              <GearIcon />
              {__('Settings', 'growfund')}
            </Link>
          </DropdownMenuItem>
          <DropdownMenuSeparator />
          <DropdownMenuItem
            onClick={() => {
              logoutMutation.mutate();
            }}
          >
            <LogOut />
            {__('Logout', 'growfund')}
          </DropdownMenuItem>
        </DropdownMenuGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default ProfileMenu;
