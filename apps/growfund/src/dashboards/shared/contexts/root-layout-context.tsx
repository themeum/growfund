import React from 'react';

import { type SidebarItem, type TopbarContent } from '@/dashboards/types/types';

interface RootLayoutContextType {
  sidebarItems: SidebarItem[];
  topbar: TopbarContent;
  setTopbar: (topbar: TopbarContent) => void;
}

const RootLayoutContext = React.createContext<RootLayoutContextType | null>(null);

const useDashboardLayoutContext = () => {
  const context = React.use(RootLayoutContext);
  if (!context) {
    throw new Error('useDashboardLayoutContext must be used within a RootLayout');
  }
  return context;
};

// eslint-disable-next-line react-refresh/only-export-components
export { RootLayoutContext, useDashboardLayoutContext };
