import { createContext, type PropsWithChildren, use } from 'react';

interface CampaignIdContextType {
  campaignId?: string;
}

const CampaignIdContext = createContext<CampaignIdContextType | null>(null);

const useCampaignId = () => {
  const context = use(CampaignIdContext);

  if (!context) {
    throw new Error('useCampaignId must be used within a CampaignIdProvider');
  }

  return context;
};

const CampaignIdProvider = ({ children, campaignId }: PropsWithChildren<CampaignIdContextType>) => {
  return <CampaignIdContext value={{ campaignId }}>{children}</CampaignIdContext>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignIdProvider, useCampaignId };
