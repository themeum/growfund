import { __ } from '@wordpress/i18n';
import React, { createContext, use } from 'react';

import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { ErrorState, ErrorStateDescription } from '@/components/error-state';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type AppConfig } from '@/features/settings/schemas/settings';
import { useAppConfigQuery } from '@/services/app-config';
import { matchQueryStatus } from '@/utils/match-query-status';

interface AppConfigContextType {
  appConfig: AppConfig;
  isDonationMode: boolean;
}

const AppConfigContext = createContext<AppConfigContextType | null>(null);

const useAppConfig = () => {
  const context = use(AppConfigContext);

  if (!context) {
    throw new Error('useAppConfig must be used within a AppConfigProvider');
  }

  return context;
};

const AppConfigProvider = ({ children }: React.PropsWithChildren) => {
  const appConfigQuery = useAppConfigQuery();

  return matchQueryStatus(appConfigQuery, {
    Loading: <LoadingSpinnerOverlay />,
    Error: (
      <ErrorState className="growfund-mt-10">
        <ErrorStateDescription>{__('Error loading app config', 'growfund')}</ErrorStateDescription>
      </ErrorState>
    ),
    Empty: (
      <EmptyState className="growfund-mt-10">
        <EmptyStateDescription className="growfund-flex growfund-flex-col growfund-items-center">
          {__('No app config', 'growfund')}
        </EmptyStateDescription>
      </EmptyState>
    ),
    Success: ({ data }) => {
      const isDonationMode = data[AppConfigKeys.DonationMode] === '1' ? true : false;
      return (
        <AppConfigContext.Provider value={{ appConfig: data, isDonationMode }}>
          {children}
        </AppConfigContext.Provider>
      );
    },
  });
};

// eslint-disable-next-line react-refresh/only-export-components
export { AppConfigProvider, useAppConfig };
