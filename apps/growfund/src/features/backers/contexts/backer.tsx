import React, { createContext, use } from 'react';

import { type BackerOverview } from '@/features/backers/schemas/backer';
import { isDefined } from '@/utils';

interface BackerContextType {
  backer: BackerOverview;
}

const BackerContext = createContext<BackerContextType | null>(null);

const useBackerContext = () => {
  const context = use(BackerContext);

  if (!isDefined(context)) {
    throw new Error('useBackerContext must be used within a BackerProvider');
  }

  return context;
};

const BackerProvider = ({
  children,
  backerOverview,
}: React.PropsWithChildren<{ backerOverview: BackerOverview }>) => {
  return <BackerContext value={{ backer: backerOverview }}>{children}</BackerContext>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { BackerProvider, useBackerContext };
