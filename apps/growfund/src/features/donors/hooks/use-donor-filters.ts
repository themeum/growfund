import { __ } from '@wordpress/i18n';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useMemo } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import { useCampaignsQuery } from '@/features/campaigns/services/campaign';
import { type DonorFilter } from '@/features/donors/schemas/donor-filter';
import { useFilterClearing } from '@/hooks/use-filter-clearing';

interface UseDonorFiltersOptions {
  form: UseFormReturn<DonorFilter>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
}

export function useDonorFilters({ form, syncQueryParams, params }: UseDonorFiltersOptions) {
  const campaignsQuery = useCampaignsQuery({
    page: 1,
    search: '',
  });

  const valueMap = useMemo(() => {
    const map: Record<keyof UseQueryStatesKeysMap, (value: string) => string> = {};

    map.campaign_id = (value: string) => {
      const campaign = campaignsQuery.data?.results.find((campaign) => campaign.id === value);
      return campaign?.title ?? value;
    };

    map.status = (value: string) => {
      const statusMap: Record<string, string> = {
        trashed: __('Trashed', 'growfund'),
      };
      return statusMap[value] ?? value;
    };

    return map;
  }, [campaignsQuery.data?.results]);

  const keyMap = useMemo(
    () => ({
      campaign_id: __('Campaign', 'growfund'),
      search: __('Search', 'growfund'),
      status: __('Status', 'growfund'),
      start_date: __('Start Date', 'growfund'),
      end_date: __('End Date', 'growfund'),
    }),
    [],
  );

  const { handleClearFilter, handleClearAllFilters } = useFilterClearing({
    form,
    syncQueryParams,
    params,
    dateFields: ['start_date', 'end_date'],
    transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>): DonorFilter => {
      return {
        search: params.search as string | undefined,
        campaign_id: params.campaign_id as string | undefined,
        status: params.status as string | undefined,
        date_range: {
          from: params.start_date as Date | undefined,
          to: params.end_date as Date | undefined,
        },
      };
    },
  });

  return {
    handleClearFilter,
    handleClearAllFilters,
    keyMap,
    valueMap,
  };
}
