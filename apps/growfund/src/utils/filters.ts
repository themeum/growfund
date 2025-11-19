import { type UseQueryStatesKeysMap, type Values } from 'nuqs';

import { getObjectKeys } from '@/utils';

function parseFilterParams(
  params: Values<UseQueryStatesKeysMap>,
  keyMap?: Record<keyof UseQueryStatesKeysMap, string>,
  valueMap?: Record<keyof UseQueryStatesKeysMap, (value: string) => string>,
) {
  if (getObjectKeys(params).length === 0) {
    return [];
  }

  const filters = [];

  for (const key in params) {
    if (key === 'pg' || !params[key]) {
      continue;
    }

    const rawValue = params[key] as string;
    const displayValue = valueMap?.[key] ? valueMap[key](rawValue) : rawValue;

    filters.push({
      key,
      key_label:
        keyMap?.[key] ??
        key
          .split('_')
          .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
          .join(' '),
      value: displayValue,
    });
  }

  return filters;
}

export { parseFilterParams };
