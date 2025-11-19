import { type IconProps } from '@radix-ui/react-icons/dist/types';
import { type ForwardRefExoticComponent, type RefAttributes } from 'react';

import { type RouteDefinition } from '@/lib/route';

interface SidebarItem {
  label: string;
  icon: ForwardRefExoticComponent<IconProps & RefAttributes<SVGSVGElement>>;
  route: RouteDefinition<string>;
  child_routes: string[];
}

interface TopbarContent {
  title: string | React.ReactNode;
  icon: ForwardRefExoticComponent<IconProps & RefAttributes<SVGSVGElement>>;
}

export { type SidebarItem, type TopbarContent };
