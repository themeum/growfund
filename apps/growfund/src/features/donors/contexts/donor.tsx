import React, { createContext, use } from 'react';

import { type DonorOverview } from '@/features/donors/schemas/donor';
import { isDefined } from '@/utils';

interface DonorContextType {
  donor: DonorOverview;
}

const DonorContext = createContext<DonorContextType | null>(null);

const useDonorContext = () => {
  const context = use(DonorContext);

  if (!isDefined(context)) {
    throw new Error('useDonorContext must be used within a DonorProvider');
  }

  return context;
};

const DonorProvider = ({
  children,
  donorOverview,
}: React.PropsWithChildren<{ donorOverview: DonorOverview }>) => {
  return <DonorContext value={{ donor: donorOverview }}>{children}</DonorContext>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { DonorProvider, useDonorContext };
