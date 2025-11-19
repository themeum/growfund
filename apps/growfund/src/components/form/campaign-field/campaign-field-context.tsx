import React, { createContext, use, useMemo, useState } from 'react';
import {
  type ControllerFieldState,
  type ControllerRenderProps,
  type FieldValues,
  type Path,
} from 'react-hook-form';

import { LIST_LIMIT } from '@/constants/list-limits';
import { CampaignStatusSchema, type Campaign } from '@/features/campaigns/schemas/campaign';
import { useCampaignsQuery } from '@/features/campaigns/services/campaign';
import { useDebounce } from '@/hooks/use-debounce';

interface CampaignFieldContextType<T extends FieldValues>
  extends React.HTMLAttributes<HTMLDivElement> {
  search: string;
  setSearch: (value: string) => void;
  campaigns: Campaign[];
  open: boolean;
  onOpenChange: (open: boolean) => void;
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
  disabled?: boolean;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const CampaignFieldContext = createContext<CampaignFieldContextType<any> | null>(null);

const useCampaignFieldContext = () => {
  const context = use(CampaignFieldContext);
  if (!context) {
    throw new Error('useCampaignFieldContext must be used within a CampaignFieldProvider');
  }
  return context;
};

const CampaignFieldProvider = <T extends FieldValues>({
  children,
  field,
  fieldState,
  disabled = false,
  ...props
}: React.PropsWithChildren<{
  field: ControllerRenderProps<T, Path<T>>;
  fieldState: ControllerFieldState;
  disabled?: boolean;
}>) => {
  const [open, setOpen] = useState(false);
  const [search, setSearch] = useState('');

  const debouncedSearch = useDebounce(search);

  const campaignsQuery = useCampaignsQuery({
    page: 1,
    search: debouncedSearch,
    per_page: LIST_LIMIT.CAMPAIGN_FIELD,
    status: [
      CampaignStatusSchema.Values.published,
      CampaignStatusSchema.Values.funded,
      CampaignStatusSchema.Values.completed,
    ].join(','),
  });

  const campaigns = useMemo(() => {
    if (!campaignsQuery.data?.results) {
      return [];
    }

    return campaignsQuery.data.results;
  }, [campaignsQuery.data]);

  const contextValue = useMemo(() => {
    return {
      search,
      setSearch,
      campaigns,
      open,
      onOpenChange: setOpen,
      field,
      fieldState,
      disabled,
    };
  }, [search, campaigns, open, field, fieldState, disabled]);

  return (
    <CampaignFieldContext value={contextValue} {...props}>
      {children}
    </CampaignFieldContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignFieldProvider, useCampaignFieldContext };
