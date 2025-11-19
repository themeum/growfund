import { createContext, useCallback, useContext, useState } from 'react';

import { noop } from '@/utils';

type CampaignMode = 'campaign' | 'donation';

interface CampaignModeContextType {
  mode: CampaignMode;
  updateMode: (mode: CampaignMode) => void;
}

const CampaignModeContext = createContext<CampaignModeContextType>({
  mode: 'campaign',
  updateMode: noop,
});

const useCampaignMode = () => useContext(CampaignModeContext);

const CampaignModeProvider = ({ children }: { children: React.ReactNode }) => {
  const [mode, setMode] = useState<CampaignMode>('campaign');

  const updateMode = useCallback((mode: CampaignMode) => {
    setMode(mode);
  }, []);

  return <CampaignModeContext value={{ mode, updateMode }}>{children}</CampaignModeContext>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignModeProvider, useCampaignMode };
