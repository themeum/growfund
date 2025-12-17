import { createContext, useContext } from 'react';

import { type MediaAttachment } from '@/schemas/media';

interface MakeFundraiserContextProps {
  isOpen: boolean;
  onOpenChange: (open: boolean) => void;
  user: {
    id: string;
    first_name: string;
    last_name: string;
    email: string;
    image?: MediaAttachment | null;
  };
}

const MakeFundraiserDialogContext = createContext<MakeFundraiserContextProps | null>(null);

const useMakeFundraiserDialog = () => {
  const context = useContext(MakeFundraiserDialogContext);

  if (!context) {
    throw new Error('MakeFundraiserDialog must be used within a MakeFundraiserDialogProvider');
  }

  return context;
};

const MakeFundraiserDialogProvider = ({
  children,
  isOpen,
  onOpenChange,
  user,
}: React.PropsWithChildren<MakeFundraiserContextProps>) => {
  return (
    <MakeFundraiserDialogContext
      value={{
        isOpen,
        onOpenChange,
        user,
      }}
    >
      {children}
    </MakeFundraiserDialogContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { MakeFundraiserDialogProvider, useMakeFundraiserDialog };
