import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { SwitchField } from '@/components/form/switch-field';
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

const DonorNotificationSettingsForm = () => {
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

  return (
    <Form {...form}>
      <form className="growfund-space-y-6">
        <SwitchField
          control={form.control}
          name="is_enabled_donor_email_campaign_post_update"
          label={__('Campaign Update', 'growfund')}
          description={__('Get notified about campaign updates.', 'growfund')}
        />
        <SwitchField
          control={form.control}
          name="is_enabled_donor_email_campaign_half_milestone_achieved"
          label={__('50% Milestone Achieved', 'growfund')}
          description={__(
            'Celebrate and inform donors when a campaign reaches 50% of its goal.',
            'growfund',
          )}
        />
      </form>
    </Form>
  );
};

export default DonorNotificationSettingsForm;
