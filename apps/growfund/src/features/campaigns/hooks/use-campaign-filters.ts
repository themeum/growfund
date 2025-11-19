import { __ } from '@wordpress/i18n';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useMemo } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import { type CampaignStatus } from '@/features/campaigns/schemas/campaign';
import { type CampaignFilter } from '@/features/campaigns/schemas/campaign-filter';
import { useFilterClearing } from '@/hooks/use-filter-clearing';

interface UseCampaignFiltersOptions {
  form: UseFormReturn<CampaignFilter>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
}

export function useCampaignFilters({ form, syncQueryParams, params }: UseCampaignFiltersOptions) {
  const valueMap = useMemo(() => {
    const map: Record<keyof UseQueryStatesKeysMap, (value: string) => string> = {};

    map.status = (value: string) => {
      const statusMap: Record<string, string> = {
        draft: __('Draft', 'growfund'),
        published: __('Published', 'growfund'),
        pending: __('Pending', 'growfund'),
        funded: __('Funded', 'growfund'),
        declined: __('Declined', 'growfund'),
        completed: __('Completed', 'growfund'),
        trashed: __('Trashed', 'growfund'),
        launched: __('Launched', 'growfund'),
      };
      return statusMap[value] ?? value;
    };

    map.start_date = (value: string) => {
      try {
        return new Date(value).toLocaleDateString();
      } catch {
        return value;
      }
    };

    map.end_date = (value: string) => {
      try {
        return new Date(value).toLocaleDateString();
      } catch {
        return value;
      }
    };

    return map;
  }, []);

  const keyMap = useMemo(
    () => ({
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
    transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>): CampaignFilter => {
      return {
        search: params.search as string | undefined,
        status: params.status as CampaignStatus | undefined,
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
