import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { getObjectKeys, isDefined } from '@/utils';

interface UseFilterClearingOptions<T extends FieldValues> {
  form: UseFormReturn<T>;
  syncQueryParams: (values: Partial<Values<UseQueryStatesKeysMap>>) => void;
  params: Values<UseQueryStatesKeysMap>;
  dateFields?: string[];
  transformParamsToFormData: (params: Values<UseQueryStatesKeysMap>) => T;
}

function makeResetSafe<T extends FieldValues>(data: T) {
  return getObjectKeys(data).reduce((newData, key) => {
    if (!isDefined(data[key])) {
      newData[key] = '' as T[Extract<keyof T, string>];
    } else {
      newData[key] = data[key];
    }
    return newData;
  }, {} as T);
}

export function useFilterClearing<T extends FieldValues>({
  form,
  syncQueryParams,
  params,
  dateFields = ['start_date', 'end_date'],
  transformParamsToFormData,
}: UseFilterClearingOptions<T>) {
  const handleClearFilter = (key: keyof UseQueryStatesKeysMap) => {
    if (dateFields.includes(key as string)) {
      const dateParams = dateFields.reduce<Record<string, undefined>>((acc, field) => {
        acc[field] = undefined;
        return acc;
      }, {});

      syncQueryParams(dateParams);
      const updatedParams = { ...params, ...dateParams };
      const formData = transformParamsToFormData(updatedParams);
      form.reset(makeResetSafe(formData));
    } else {
      syncQueryParams({ [key]: undefined });
      const updatedParams = { ...params, [key]: undefined };
      const formData = transformParamsToFormData(updatedParams);
      form.reset(makeResetSafe(formData));
    }
  };

  const handleClearAllFilters = (newParams: Values<UseQueryStatesKeysMap>) => {
    syncQueryParams(newParams);
    const formData = transformParamsToFormData(newParams);
    form.reset(makeResetSafe(formData));
  };

  return {
    handleClearFilter,
    handleClearAllFilters,
  };
}
