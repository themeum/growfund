import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router';
import { toast } from 'sonner';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import EmailContentTemplateForm from '@/features/settings/components/templates/email-content-template-form';
import {
    EmailContentFormSchema,
    type EmailContent,
    type EmailContentForm,
} from '@/features/settings/schemas/email-content';
import { useStoreEmailContentMutation } from '@/services/app-config';
import { isDefined } from '@/utils';

const EmailLayoutContent = ({
  pageHeader,
  emailContent,
  emailOptionKey,
}: {
  pageHeader: string;
  emailContent?: EmailContent | null;
  emailOptionKey: string;
}) => {
  const navigate = useNavigate();
  const form = useForm<EmailContentForm>({
    resolver: zodResolver(EmailContentFormSchema),
  });

  useEffect(() => {
    if (isDefined(emailContent)) {
      form.reset.call(null, {
        subject: emailContent.subject,
        heading: emailContent.heading,
        message: emailContent.message,
      });
    }
  }, [emailContent, form]);

  const storeEmailContentMutation = useStoreEmailContentMutation();

  const handleFormSubmit = () => {
    void form.handleSubmit(
      (values) => {
        storeEmailContentMutation.mutate(
          {
            key: emailOptionKey,
            data: values,
          },
          {
            onSuccess() {
              toast.success(__('Content updated successfully', 'growfund'));
            },
          },
        );
      },
      (errors) => {
        console.error(errors);
      },
    )();
  };

  return (
    <Page>
      <PageHeader
        name={pageHeader}
        onGoBack={() => void navigate(-1)}
        variant="fluid"
        action={
          <div className="growfund-flex growfund-items-center growfund-gap-3">
            <Button
              variant="outline"
              onClick={() => {
                form.reset();
              }}
              disabled={storeEmailContentMutation.isPending || !form.formState.isDirty}
            >
              {__('Discard', 'growfund')}
            </Button>
            <Button
              onClick={handleFormSubmit}
              loading={storeEmailContentMutation.isPending}
              disabled={storeEmailContentMutation.isPending || !form.formState.isDirty}
            >
              {__('Save', 'growfund')}
            </Button>
          </div>
        }
      />
      <PageContent>
        <Container className="growfund-mt-10">
          <Form {...form}>
            <EmailContentTemplateForm emailContent={emailContent} emailOptionKey={emailOptionKey} />
          </Form>
        </Container>
      </PageContent>
    </Page>
  );
};

export default EmailLayoutContent;
