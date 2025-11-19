import { type UseQueryResult } from '@tanstack/react-query';
import { useMemo } from 'react';

import { type Option } from '@/types';

const useQueryToOption = <T extends object>(
  query: UseQueryResult<T[]>,
  valueKey: keyof T,
  labelKey: keyof T,
  excludeValue?: string,
) => {
  return useMemo<Option<string>[]>(() => {
    if (!query.data) {
      return [];
    }

    return query.data
      .filter((item) => {
        return item[valueKey] !== excludeValue;
      })
      .map<Option<string>>((item) => ({
        value: item[valueKey] as string,
        label: item[labelKey] as string,
      }));
  }, [query.data, valueKey, labelKey, excludeValue]);
};

export { useQueryToOption };
