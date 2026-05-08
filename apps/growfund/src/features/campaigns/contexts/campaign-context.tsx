import {
  createContext,
  type Dispatch,
  type PropsWithChildren,
  type SetStateAction,
  use,
  useEffect,
  useState,
} from 'react';

import { type Campaign } from '@/features/campaigns/schemas/campaign';

interface CampaignContextType {
  campaign: Campaign;
  updateCampaign: Dispatch<SetStateAction<Campaign>>;
}

const CampaignContext = createContext<CampaignContextType | null>(null);

const useCampaign = () => {
  const context = use(CampaignContext);

  if (!context) {
    throw new Error('useCampaign must be used within a CampaignProvider');
  }

  return context;
};

const CampaignProvider = ({ children, campaign }: PropsWithChildren<{ campaign: Campaign }>) => {
  const [campaignState, setCampaignState] = useState<Campaign>(campaign);

  useEffect(() => {
    setCampaignState(campaign);
  }, [campaign]);

  return (
    <CampaignContext value={{ campaign: campaignState, updateCampaign: setCampaignState }}>
      {children}
    </CampaignContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignProvider, useCampaign };
