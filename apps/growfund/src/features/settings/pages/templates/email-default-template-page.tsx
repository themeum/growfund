import { zodResolver } from '@hookform/resolvers/zod';
import { useEffect, useMemo, useRef } from 'react';
import { useForm } from 'react-hook-form';

import { TemplateForm } from '@/components/template-form/template-form';
import { OptionKeys } from '@/constants/option-keys';
import EmailTemplateForm from '@/features/settings/components/templates/email-default-template/email-default-template-form';
import { useTemplateLayoutContext } from '@/features/settings/context/template-layout-context';
import { useUpdateTemplateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  EmailTemplateFormSchema,
  type EmailTemplate,
  type EmailTemplateForm as EmailTemplateFormType,
} from '@/features/settings/schemas/email-default-template';
import { useGetOptionQuery } from '@/services/app-config';

const EmailDefaultTemplatePage = () => {
  const formRef = useRef<HTMLFormElement | null>(null);

  const emailTemplateQuery = useGetOptionQuery(OptionKeys.EMAIL_NOTIFICATION_TEMPLATE);

  const emailTemplate = useMemo(() => {
    if (!emailTemplateQuery.data) {
      return {} as EmailTemplate;
    }
    return emailTemplateQuery.data as EmailTemplate;
  }, [emailTemplateQuery.data]);

  const form = useForm<EmailTemplateFormType>({
    resolver: zodResolver(EmailTemplateFormSchema),
  });

  useEffect(() => {
    if (Object.keys(emailTemplate).length !== 0) {
      form.reset.call(null, emailTemplate);
    }
  }, [emailTemplate, form.reset]);

  const { registerForm } = useTemplateLayoutContext<EmailTemplateFormType>();

  useEffect(() => {
    const cleanup = registerForm(OptionKeys.EMAIL_NOTIFICATION_TEMPLATE, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateTemplateDirtyState(form);

  return (
    <TemplateForm form={form} ref={formRef}>
      <EmailTemplateForm />
    </TemplateForm>
  );
};

export default EmailDefaultTemplatePage;
