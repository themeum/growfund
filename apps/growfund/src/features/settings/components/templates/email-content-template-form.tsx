import { __ } from '@wordpress/i18n';
import { RotateCw } from 'lucide-react';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { toast } from 'sonner';

import { EditorField } from '@/components/form/editor-field';
import { TextField } from '@/components/form/text-field';
import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { OptionKeys } from '@/constants/option-keys';
import {
    EmailAdditionalContent,
    EmailCard,
    EmailContent,
    EmailFooter,
    EmailHeader,
    EmailLogo,
    EmailMessage,
} from '@/features/settings/components/ui/email-preview';
import {
    type EmailContentForm,
    type EmailContent as EmailContentType,
} from '@/features/settings/schemas/email-content';
import { useGetOptionQuery, useRestoreEmailContentMutation } from '@/services/app-config';
import { isDefined } from '@/utils';

const EmailContentTemplateForm = ({
  emailContent,
  emailOptionKey,
}: {
  emailContent?: EmailContentType | null;
  emailOptionKey: string;
}) => {
  const allowRestore =
    !isDefined(emailContent) ||
    Object.keys(emailContent).length === 0 ||
    (isDefined(emailContent.is_default) && !emailContent.is_default);

  const form = useFormContext<EmailContentForm>();

  const description = useWatch({
    control: form.control,
    name: 'message',
  });
  const heading = useWatch({
    control: form.control,
    name: 'heading',
  });

  const emailTemplateQuery = useGetOptionQuery(OptionKeys.EMAIL_NOTIFICATION_TEMPLATE);

  const restoreEmailContentMutation = useRestoreEmailContentMutation();
  const handleRestoreToDefault = () => {
    restoreEmailContentMutation.mutate(
      {
        key: emailOptionKey,
      },
      {
        onSuccess() {
          toast.success(__('Restored successfully', 'growfund'));
        },
      },
    );
  };

  const emailTemplate = useMemo(() => {
    if (!emailTemplateQuery.data) {
      return {};
    }
    return emailTemplateQuery.data;
  }, [emailTemplateQuery.data]);

  if (emailTemplateQuery.isLoading || emailTemplateQuery.isPending) {
    return <LoadingSpinnerOverlay />;
  }

  return (
    <div className="growfund-flex growfund-justify-center growfund-gap-8 growfund-px-12 growfund-py-8">
      <div className="growfund-w-[32.5rem] growfund-space-y-4">
        <Card className="growfund-py-4">
          <CardContent className="growfund-bg-background-surface growfund-space-y-4">
            <TextField control={form.control} name="subject" label={__('Subject', 'growfund')} />
            {emailOptionKey !== 'growfund_donor_email_tribute_mail' && (
              <TextField
                control={form.control}
                name="heading"
                label={__('Email Heading', 'growfund')}
              />
            )}
            <EditorField
              control={form.control}
              name="message"
              label={__('Content', 'growfund')}
              placeholder={__('Enter email content', 'growfund')}
              shortCodes={emailContent?.shortcodes ?? []}
            />
          </CardContent>
        </Card>
        {allowRestore && (
          <div className="growfund-w-full growfund-flex growfund-justify-end">
            <Button
              variant="ghost"
              className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-tiny growfund-text-fg-primary"
              onClick={handleRestoreToDefault}
            >
              <RotateCw className="growfund-size-4" />
              <span>{__('Restore to Default', 'growfund')}</span>
            </Button>
          </div>
        )}
      </div>
      <div className="growfund-w-[31rem] growfund-p-1 growfund-space-y-4">
        <div className="growfund-w-full growfund-flex growfund-items-center growfund-justify-between">
          <div className="growfund-flex growfund-items-center growfund-gap-4">
            <h6 className="growfund-typo-h6 growfund-text-fg-primary">{__('Email Preview', 'growfund')}</h6>
          </div>
        </div>
        <EmailCard emailTemplate={emailTemplate}>
          <EmailLogo />
          <EmailContent>
            {isDefined(heading) && <EmailHeader heading={heading} />}
            {description ? (
              <EmailMessage>{description}</EmailMessage>
            ) : (
              <div className="growfund-w-full growfund-flex growfund-justify-center growfund-text-fg-subdued growfund-typo-tiny">
                {__('Add your content here', 'growfund')}
              </div>
            )}
            <EmailAdditionalContent />
          </EmailContent>
          <EmailFooter />
        </EmailCard>
      </div>
    </div>
  );
};

export default EmailContentTemplateForm;
