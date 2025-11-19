import { useQueryStates, type UseQueryStatesKeysMap, type Values } from 'nuqs';
import { useCallback, useEffect, useMemo, useRef } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { useDetectRouteChange } from '@/contexts/detect-route-change-context';
import { debounce, getObjectEntries, isDeepEqual } from '@/utils';

interface UseFormQuerySyncOptions<T extends FieldValues, K extends UseQueryStatesKeysMap> {
  keyMap: K;
  form: UseFormReturn<T>;
  paramsToForm: (params: Values<K>) => T;
  formToParams: (formData: T) => Partial<Values<K>>;
  watchFields?: (keyof T)[];
}

const useFormQuerySync = <T extends FieldValues, K extends UseQueryStatesKeysMap>({
  keyMap,
  form,
  paramsToForm,
  formToParams,
  watchFields,
}: UseFormQuerySyncOptions<T, K>) => {
  const [params, setParams] = useQueryStates(keyMap);
  const previousParamsRef = useRef(params);
  const isInitialMount = useRef(true);
  const { isRouteChanged } = useDetectRouteChange();

  const syncQueryParams = useCallback(
    (values: Partial<Values<K>>) => {
      const newValues = getObjectEntries(values).reduce<Partial<Values<K>>>((acc, [key, value]) => {
        return { ...acc, [key]: value ?? null };
      }, {});
      void setParams(newValues as Parameters<typeof setParams>[0]);
    },
    [setParams],
  );

  const debouncedSyncQueryParams = useMemo(() => debounce(syncQueryParams, 300), [syncQueryParams]);

  const resetFormAndParams = useCallback(() => {
    const resetValues = getObjectEntries(keyMap).reduce<Partial<Values<K>>>((acc, [key]) => {
      return { ...acc, [key]: null };
    }, {});

    void setParams(resetValues as Parameters<typeof setParams>[0]);
    form.reset.call(null);
  }, [keyMap, setParams, form.reset]);

  useEffect(() => {
    if (isRouteChanged && !isInitialMount.current) {
      resetFormAndParams();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isRouteChanged]);

  useEffect(() => {
    if (isInitialMount.current) {
      form.reset.call(null, paramsToForm(params));
      isInitialMount.current = false;
      return;
    }

    const currentFormData = paramsToForm(params);
    const previousFormData = paramsToForm(previousParamsRef.current);
    const hasParamsChanged = !isDeepEqual(currentFormData, previousFormData);

    if (hasParamsChanged) {
      form.reset.call(null, currentFormData);
    }

    previousParamsRef.current = params;
  }, [params, form.reset, paramsToForm]);

  useEffect(() => {
    if (!watchFields || watchFields.length === 0) return;

    const subscription = form.watch.call(null, (formData) => {
      const queryParams = formToParams(formData as T);
      debouncedSyncQueryParams(queryParams);
    });

    return () => {
      subscription.unsubscribe();
    };
  }, [form.watch, formToParams, debouncedSyncQueryParams, watchFields]);

  return { params, syncQueryParams } as const;
};

export { useFormQuerySync };
