import { type Ref, useCallback } from 'react';

import { isDefined } from '@/utils';

function useMergedRef<T>(...refs: Ref<T>[]): (instance: T | null) => void {
  return useCallback(
    (instance: T | null) => {
      refs.forEach((ref) => {
        if (!ref) return;
        if (typeof ref === 'function') {
          ref(instance);
        } else if (isDefined(ref) && 'current' in ref) {
          ref.current = instance;
        }
      });
    },
    [refs],
  );
}

export { useMergedRef };
