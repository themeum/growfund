import { Link } from 'react-router';

import { type SidebarItem } from '@/dashboards/types/types';
import { useCurrentPath } from '@/hooks/use-current-path';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const SidebarItem = ({ item }: { item: SidebarItem }) => {
  const currentPath = useCurrentPath();
  const isActive = !isDefined(currentPath)
    ? false
    : currentPath === item.route.template || item.child_routes.includes(currentPath);

  const IconComp = item.icon;
  return (
    <div className="growfund-px-3">
      <Link
        to={item.route.buildLink()}
        className={cn(
          'growfund-flex growfund-items-center growfund-gap-2 growfund-typo-small growfund-text-fg-secondary growfund-min-h-8 growfund-px-3 growfund-py-2 growfund-rounded-lg growfund-relative growfund-group/sidebar-item',
          isActive
            ? 'growfund-bg-background-fill-success-var growfund-text-fg-success-var [&>svg]:growfund-text-icon-success-var'
            : 'hover:growfund-bg-background-secondary hover:growfund-text-fg-secondary',
        )}
      >
        <IconComp
          className={cn(
            'growfund-size-4 growfund-shrink-0 growfund-text-icon-primary',
            !isActive && 'group-hover/sidebar-item:growfund-text-fg-secondary',
          )}
        />
        <span>{item.label}</span>
        {isActive && (
          <span className="growfund-absolute growfund-w-1 growfund-h-6 growfund-left-0 growfund-top-1/2 growfund--translate-y-1/2 growfund-rounded-lg growfund-bg-background-fill-brand-var" />
        )}
      </Link>
    </div>
  );
};

export default SidebarItem;
