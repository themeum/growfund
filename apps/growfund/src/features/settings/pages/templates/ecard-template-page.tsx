import { zodResolver } from '@hookform/resolvers/zod';
import { useEffect, useMemo, useRef } from 'react';
import { useForm } from 'react-hook-form';

import { TemplateForm } from '@/components/template-form/template-form';
import { OptionKeys } from '@/constants/option-keys';
import EcardTemplateForm from '@/features/settings/components/templates/campaign-ecard/ecard-template-form';
import { useTemplateLayoutContext } from '@/features/settings/context/template-layout-context';
import { useUpdateTemplateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import { type EcardTemplate, EcardTemplateFormSchema } from '@/features/settings/schemas/ecard';
import { getDefaults } from '@/lib/zod';
import { useGetOptionQuery } from '@/services/app-config';

const EcardTemplatePage = () => {
  const formRef = useRef<HTMLFormElement | null>(null);

  const ecardTemplateQuery = useGetOptionQuery(OptionKeys.ECARD_TEMPLATE);

  const ecardTemplate = useMemo(() => {
    if (!ecardTemplateQuery.data) {
      return {} as EcardTemplate;
    }
    return ecardTemplateQuery.data as EcardTemplate;
  }, [ecardTemplateQuery.data]);

  const form = useForm<EcardTemplateForm>({
    resolver: zodResolver(EcardTemplateFormSchema),
    defaultValues: getDefaults(EcardTemplateFormSchema),
  });

  useEffect(() => {
    if (Object.keys(ecardTemplate).length !== 0) {
      form.reset.call(null, ecardTemplate);
    }
  }, [ecardTemplate, form.reset]);

  const { registerForm } = useTemplateLayoutContext<EcardTemplateForm>();

  useEffect(() => {
    const cleanup = registerForm(OptionKeys.ECARD_TEMPLATE, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateTemplateDirtyState(form);

  return (
    <TemplateForm form={form} ref={formRef}>
      <EcardTemplateForm shortCodes={ecardTemplate.short_codes} />
    </TemplateForm>
  );
};

export default EcardTemplatePage;
