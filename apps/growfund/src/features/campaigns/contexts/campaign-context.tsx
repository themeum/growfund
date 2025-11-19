import { createContext, type PropsWithChildren, use } from 'react';

import { type Campaign } from '@/features/campaigns/schemas/campaign';

interface CampaignContextType {
  campaign: Campaign;
}

const CampaignContext = createContext<CampaignContextType | null>(null);

const useCampaign = () => {
  const context = use(CampaignContext);

  if (!context) {
    throw new Error('useCampaign must be used within a CampaignProvider');
  }

  return context;
};

const CampaignProvider = ({ children, campaign }: PropsWithChildren<CampaignContextType>) => {
  return <CampaignContext value={{ campaign }}>{children}</CampaignContext>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignProvider, useCampaign };
