import { __ } from '@wordpress/i18n';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useMemo } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import { useAppConfig } from '@/contexts/app-config';
import { useCampaignsQuery } from '@/features/campaigns/services/campaign';
import { DonationStatusSchema } from '@/features/donations/schemas/donation';
import { type DonationFiltersForm } from '@/features/donations/schemas/donation-filter';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useFilterClearing } from '@/hooks/use-filter-clearing';
import { useQueryHook } from '@/lib/query-registry';
import { type PaginatedResponse } from '@/types';

interface UseDonationFiltersOptions {
  form: UseFormReturn<DonationFiltersForm>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
}

export function useDonationFilters({ form, syncQueryParams, params }: UseDonationFiltersOptions) {
  const { appConfig } = useAppConfig();

  const campaignsQuery = useCampaignsQuery({
    page: 1,
    search: '',
  });

  const useFundsQuery = useQueryHook<unknown, PaginatedResponse<Record<string, unknown>>>(
    'useFundsQuery',
  );
  const fundsQuery = useFundsQuery?.(
    {
      page: 1,
      search: '',
    },
    !!appConfig[AppConfigKeys.Campaign]?.allow_fund,
  );

  const valueMap = useMemo(() => {
    const map: Record<keyof UseQueryStatesKeysMap, (value: string) => string> = {};

    map.campaign_id = (value: string) => {
      const campaign = campaignsQuery.data?.results.find((campaign) => campaign.id === value);
      return campaign?.title ?? value;
    };

    map.fund_id = (value: string) => {
      const fund = fundsQuery?.data?.results.find((fund) => String(fund.id) === value);
      return fund?.title ? (fund.title as string) : value;
    };

    map.status = (value: string) => {
      const statusMap: Record<string, string> = {
        pending: __('Pending', 'growfund'),
        completed: __('Completed', 'growfund'),
        cancelled: __('Cancelled', 'growfund'),
        failed: __('Failed', 'growfund'),
        refunded: __('Refunded', 'growfund'),
        trashed: __('Trashed', 'growfund'),
      };
      return statusMap[value] ?? value;
    };

    return map;
  }, [campaignsQuery.data?.results, fundsQuery?.data?.results]);

  const keyMap = useMemo(
    () => ({
      campaign_id: __('Campaign', 'growfund'),
      fund_id: __('Fund', 'growfund'),
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
    transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>): DonationFiltersForm => {
      return {
        search: params.search as string | undefined,
        status: DonationStatusSchema.safeParse(params.status).data ?? undefined,
        fund_id: params.fund_id as string | undefined,
        campaign_id: params.campaign_id as string | undefined,
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
