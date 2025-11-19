import { useCallback, useRef, useState } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { noop } from '@/utils';

const useManageMultiform = <T extends FieldValues, K>() => {
  const forms = useRef<Map<K, UseFormReturn<T>>>(new Map());
  const [isCurrentFormDirty, setIsCurrentFormDirty] = useState(false);
  const [currentKey, setCurrentKey] = useState<K | null>(null);

  const unregisterForm = useCallback((key: K) => {
    if (!forms.current.has(key)) {
      return;
    }

    forms.current.delete(key);
  }, []);

  const registerForm = useCallback(
    (key: K, form: UseFormReturn<T>) => {
      if (forms.current.has(key)) {
        return noop;
      }

      forms.current.set(key, form);
      setCurrentKey(key);

      return () => {
        unregisterForm(key);
        setCurrentKey(null);
      };
    },
    [unregisterForm],
  );

  const getCurrentForm = useCallback(() => {
    if (forms.current.size === 0) {
      return null;
    }

    const lastKey = [...forms.current.keys()].pop();

    if (!lastKey) {
      return null;
    }

    return forms.current.get(lastKey) ?? null;
  }, []);

  const getCurrentKey = useCallback(() => {
    return currentKey;
  }, [currentKey]);

  return {
    registerForm,
    getCurrentForm,
    getCurrentKey,
    isCurrentFormDirty,
    setIsCurrentFormDirty,
  };
};

export { useManageMultiform };
