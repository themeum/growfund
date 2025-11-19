import { zodResolver } from '@hookform/resolvers/zod';
import { EnvelopeClosedIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { SwitchField } from '@/components/form/switch-field';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import {
    FormKeys,
    useUserSettingsContext,
} from '@/dashboards/shared/contexts/user-settings-context';
import {
    type DonorNotificationsForm,
    DonorNotificationsFormSchema,
} from '@/dashboards/shared/schemas/user';
import { getDefaults } from '@/lib/zod';

const DonorNotificationsPage = () => {
  const { registerForm, setIsCurrentFormDirty, user } = useUserSettingsContext();

  const form = useForm<DonorNotificationsForm>({
    resolver: zodResolver(DonorNotificationsFormSchema),
    defaultValues: getDefaults(DonorNotificationsFormSchema),
  });

  useEffect(() => {
    if (user?.notification_settings) {
      form.reset.call(null, user.notification_settings);
    }
  }, [user, form.reset]);

  useEffect(() => {
    const cleanup = registerForm(FormKeys.Notifications, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useEffect(() => {
    setIsCurrentFormDirty(form.formState.isDirty);
  }, [form.formState.isDirty, setIsCurrentFormDirty]);

  if (!user) {
    return null;
  }

  return (
    <div className="growfund-w-full growfund-space-y-3">
      <p className="growfund-typo-small growfund-font-semibold growfund-text-fg-primary growfund-mt-2">
        {__('Notifications', 'growfund')}
      </p>
      <Card>
        <CardHeader>
          <CardTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <EnvelopeClosedIcon />
            {__('Email Notifications', 'growfund')}
          </CardTitle>
          <CardDescription>
            {__('Choose which email notifications you want to receive.', 'growfund')}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Form {...form}>
            <form className="growfund-space-y-6">
              <SwitchField
                control={form.control}
                name="is_enabled_donor_email_campaign_post_update"
                label={__('Campaign Updates', 'growfund')}
                description={__('Receive an email when campaign status is updated.', 'growfund')}
              />
              <SwitchField
                control={form.control}
                name="is_enabled_donor_email_campaign_half_milestone_achieved"
                label={__('Campaign 50% Funded', 'growfund')}
                description={__(
                  'Celebrate and inform backers when a campaign reaches 50% of its goal.',
                  'growfund',
                )}
              />
            </form>
          </Form>
        </CardContent>
      </Card>
    </div>
  );
};

export default DonorNotificationsPage;
