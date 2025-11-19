import React, { use, useCallback, useRef, useState } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { OptionKeys } from '@/constants/option-keys';
import { noop } from '@/utils';

interface LayoutContextType<T extends FieldValues> {
  registerForm: (key: OptionKeys, form: UseFormReturn<T>) => () => void;
  getCurrentForm: () => UseFormReturn<T> | null;
  getCurrentKey: () => OptionKeys | null;
  isCurrentFormDirty: boolean;
  setIsCurrentFormDirty: (state: boolean) => void;
  isLoading: boolean;
  setIsLoading: (state: boolean) => void;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const LayoutContext = React.createContext<LayoutContextType<any> | null>(null);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const TemplateLayoutProvider = ({ children }: React.ComponentProps<any>) => {
  const forms = useRef<Map<OptionKeys, UseFormReturn>>(new Map());
  const [isCurrentFormDirty, setIsCurrentFormDirty] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const unregisterForm = useCallback((key: OptionKeys) => {
    if (!forms.current.has(key)) {
      return;
    }

    forms.current.delete(key);
  }, []);

  const registerForm = useCallback(
    (key: OptionKeys, form: UseFormReturn) => {
      if (forms.current.has(key)) {
        return noop;
      }

      forms.current.set(key, form);

      return () => {
        unregisterForm(key);
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
    if (forms.current.size === 0) {
      return null;
    }

    const lastKey = [...forms.current.keys()].pop();

    if (!lastKey) {
      return null;
    }

    return lastKey;
  }, []);

  return (
    <LayoutContext
      value={{
        registerForm,
        getCurrentForm,
        getCurrentKey,
        isCurrentFormDirty,
        setIsCurrentFormDirty,
        isLoading,
        setIsLoading,
      }}
    >
      {children}
    </LayoutContext>
  );
};

const useTemplateLayoutContext = <T extends FieldValues = FieldValues>() => {
  const context = use(LayoutContext);

  if (!context) {
    throw new Error('useTemplateLayoutContext must be used within a TemplateLayoutProvider');
  }

  return context as LayoutContextType<T>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { OptionKeys, TemplateLayoutProvider, useTemplateLayoutContext };
