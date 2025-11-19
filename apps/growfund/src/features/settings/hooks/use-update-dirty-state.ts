import { useEffect } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { useSettingsContext } from '@/features/settings/context/settings-context';
import { useTemplateLayoutContext } from '@/features/settings/context/template-layout-context';

const useUpdateDirtyState = <T extends FieldValues>(form: UseFormReturn<T>) => {
  const { setIsCurrentFormDirty } = useSettingsContext();
  const { isDirty } = form.formState;

  useEffect(() => {
    setIsCurrentFormDirty(isDirty);
  }, [isDirty, setIsCurrentFormDirty]);

  return { isDirty };
};

const useUpdateTemplateDirtyState = <T extends FieldValues>(form: UseFormReturn<T>) => {
  const { setIsCurrentFormDirty } = useTemplateLayoutContext();
  const { isDirty } = form.formState;

  useEffect(() => {
    setIsCurrentFormDirty(isDirty);
  }, [isDirty, setIsCurrentFormDirty]);

  return { isDirty };
};

export { useUpdateDirtyState, useUpdateTemplateDirtyState };
