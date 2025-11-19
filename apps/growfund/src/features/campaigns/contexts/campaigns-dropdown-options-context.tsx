import {
  createContext,
  type Dispatch,
  type PropsWithChildren,
  type SetStateAction,
  use,
} from 'react';

interface CampaignsDropdownOptionsContextType {
  campaign: {
    id: string;
    title: string;
    image?: string | null;
    created_by?: string | null;
  };
  setIsDropdownOpen: Dispatch<SetStateAction<boolean>>;
}

const CampaignsDropdownOptionsContext = createContext<CampaignsDropdownOptionsContextType | null>(
  null,
);

const useCampaignsDropdownOptions = () => {
  const context = use(CampaignsDropdownOptionsContext);

  if (!context) {
    throw new Error(
      'useCampaignsDropdownOptions must be used within a CampaignsDropdownOptionsProvider',
    );
  }

  return context;
};

const CampaignsDropdownOptionsProvider = ({
  children,
  campaign,
  setIsDropdownOpen,
}: PropsWithChildren<CampaignsDropdownOptionsContextType>) => {
  return (
    <CampaignsDropdownOptionsContext value={{ campaign, setIsDropdownOpen }}>
      {children}
    </CampaignsDropdownOptionsContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignsDropdownOptionsProvider, useCampaignsDropdownOptions };
