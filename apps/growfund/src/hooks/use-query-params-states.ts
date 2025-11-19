import { useQueryStates, type UseQueryStatesKeysMap, type UseQueryStatesReturn } from 'nuqs';
import { useCallback } from 'react';

import { getObjectEntries, isDefined } from '@/utils';

const useQueryParamsStates = <KeyMap extends UseQueryStatesKeysMap>(keyMap: KeyMap) => {
  const [params, setParams] = useQueryStates(keyMap);

  const readQueryParams = useCallback(
    (
      callback?: (params: UseQueryStatesReturn<KeyMap>[0]) => Record<keyof KeyMap[number], unknown>,
    ) => {
      if (!isDefined(callback)) {
        return params;
      }
      return callback(params);
    },
    [params],
  );

  const syncQueryParams = useCallback(
    (values: Partial<UseQueryStatesReturn<KeyMap>[0]>) => {
      const newValues = getObjectEntries(values).reduce<Partial<UseQueryStatesReturn<KeyMap>[0]>>(
        (acc, [key, value]) => {
          return { ...acc, [key]: !value ? null : value };
        },
        {},
      );
      void setParams(newValues);
    },
    [setParams],
  );

  return { params, setParams, readQueryParams, syncQueryParams };
};

export { useQueryParamsStates };
