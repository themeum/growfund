import { __ } from '@wordpress/i18n';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useMemo } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import { type TagFilter } from '@/features/tags/schemas/tags-filter';
import { useFilterClearing } from '@/hooks/use-filter-clearing';

interface UseTagFiltersOptions {
  form: UseFormReturn<TagFilter>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
}

export function useTagFilters({ form, syncQueryParams, params }: UseTagFiltersOptions) {
  const valueMap = useMemo(() => {
    const map: Record<keyof UseQueryStatesKeysMap, (value: string) => string> = {};

    return map;
  }, []);

  const keyMap = useMemo(
    () => ({
      search: __('Search', 'growfund'),
    }),
    [],
  );

  const { handleClearFilter, handleClearAllFilters } = useFilterClearing({
    form,
    syncQueryParams,
    params,
    dateFields: [],
    transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>): TagFilter => {
      return {
        search: params.search as string | undefined,
        action: params.action as 'delete' | undefined,
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
