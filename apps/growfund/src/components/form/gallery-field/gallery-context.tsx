import type { UniqueIdentifier } from '@dnd-kit/core';
import { createContext, useContext, useState } from 'react';

export interface GalleryContextType {
  checkedItems: UniqueIdentifier[];
  setCheckedItems: React.Dispatch<React.SetStateAction<UniqueIdentifier[]>>;
}

const GalleryContext = createContext<GalleryContextType | undefined>(undefined);

export const GalleryProvider = ({ children }: { children: React.ReactNode }) => {
  const [checkedItems, setCheckedItems] = useState<UniqueIdentifier[]>([]);
  const value = { checkedItems, setCheckedItems };

  return <GalleryContext.Provider value={value}>{children}</GalleryContext.Provider>;
};

// eslint-disable-next-line react-refresh/only-export-components
export const useGalleryContext = () => {
  const context = useContext(GalleryContext);

  if (context === undefined) {
    throw new Error('useGalleryContext must be used within a GalleryProvider');
  }

  return context;
};
