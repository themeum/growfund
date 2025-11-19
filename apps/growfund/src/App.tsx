import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { NuqsAdapter } from 'nuqs/adapters/react-router/v7';
import { useEffect } from 'react';
import { RouterProvider } from 'react-router';

import { routes } from '@/app/routes';
import { Toaster } from '@/components/ui/sonner';
import { AppConfigProvider } from '@/contexts/app-config';
import { CurrentUserProvider } from '@/contexts/current-user-context';
import { ConsentDialogProvider } from '@/features/campaigns/contexts/consent-dialog-context';
import { isDefined } from '@/utils';

import { CampaignModeProvider } from './contexts/campaign-mode';

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
      refetchOnWindowFocus: false,
      networkMode: 'always',
      staleTime: 20 * 1000, // 20 seconds
    },
    mutations: {
      retry: false,
      networkMode: 'always',
    },
  },
});

function App() {
  // Dispatch a custom 'OnGrowfundAppLoaded' event when the React application is mounted
  // This event signals that the app is ready to interact with the WordPress admin interface
  // When received, the event handler will:
  // 1. Remove the default WordPress admin sidebar and topbar
  // 2. Isolate the React application view from WordPress UI elements
  // 3. Allow the React components to take full control of the admin layout
  useEffect(() => {
    window.dispatchEvent(new Event('OnGrowfundAppLoaded'));
  }, []);

  if (!isDefined(window.growfund) || window.growfund.as_guest) {
    return (
      <QueryClientProvider client={queryClient}>
        <AppConfigProvider>
          <ConsentDialogProvider>
            <NuqsAdapter>
              <RouterProvider router={routes} />
            </NuqsAdapter>
          </ConsentDialogProvider>
          <Toaster />
        </AppConfigProvider>
      </QueryClientProvider>
    );
  }

  return (
    <QueryClientProvider client={queryClient}>
      <CurrentUserProvider>
        <AppConfigProvider>
          <CampaignModeProvider>
            <ConsentDialogProvider>
              <NuqsAdapter>
                <RouterProvider router={routes} />
              </NuqsAdapter>
            </ConsentDialogProvider>
            <Toaster />
          </CampaignModeProvider>
        </AppConfigProvider>
      </CurrentUserProvider>
    </QueryClientProvider>
  );
}

export default App;
