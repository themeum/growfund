import React, { use, useState } from 'react';

interface SidebarContextType {
  isOpenSidebar: boolean;
  setOpenSidebar: (state: boolean) => void;
}

const SidebarContext = React.createContext<SidebarContextType | null>(null);

// eslint-disable-next-line react-refresh/only-export-components
export const useSidebarContext = () => {
  const context = use(SidebarContext);

  if (!context) {
    throw new Error('useSidebarContext must be used within SidebarProvider');
  }

  return context;
};

const SidebarProvider = ({ children }: React.PropsWithChildren) => {
  const [isOpenSidebar, setOpenSidebar] = useState(false);

  return (
    <SidebarContext.Provider value={{ isOpenSidebar, setOpenSidebar }}>
      {children}
    </SidebarContext.Provider>
  );
};

export { SidebarProvider };
