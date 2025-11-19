import { __ } from '@wordpress/i18n';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useMemo } from 'react';
import { type UseFormReturn } from 'react-hook-form';

import { type CategoryFilter } from '@/features/categories/schemas/categories-filter';
import { useFilterClearing } from '@/hooks/use-filter-clearing';

interface UseCategoryFiltersOptions {
  form: UseFormReturn<CategoryFilter>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
}

export function useCategoryFilters({ form, syncQueryParams, params }: UseCategoryFiltersOptions) {
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
    transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>): CategoryFilter => {
      return {
        search: params.search as string | undefined,
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
