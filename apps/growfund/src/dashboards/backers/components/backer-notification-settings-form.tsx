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
    type BackerNotificationsForm,
    BackerNotificationsFormSchema,
} from '@/dashboards/shared/schemas/user';
import { getDefaults } from '@/lib/zod';

const BackerNotificationSettingsForm = () => {
  const { registerForm, setIsCurrentFormDirty, user } = useUserSettingsContext();

  const form = useForm<BackerNotificationsForm>({
    resolver: zodResolver(BackerNotificationsFormSchema),
    defaultValues: getDefaults(BackerNotificationsFormSchema),
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
          name="is_enabled_backer_email_campaign_post_update"
          label={__('Campaign Updates', 'growfund')}
          description={__('Receive an email when a campaign is updated.', 'growfund')}
        />
        <SwitchField
          control={form.control}
          name="is_enabled_backer_email_campaign_half_funded"
          label={__('Campaign 50% Funded', 'growfund')}
          description={__(
            'Celebrate and inform backers when a campaign reaches 50% of its goal.',
            'growfund',
          )}
        />
        <SwitchField
          control={form.control}
          name="is_enabled_backer_email_reward_delivered"
          label={__('Rewards Delivered', 'growfund')}
          description={__('Receive an email when a reward is delivered.', 'growfund')}
        />
      </form>
    </Form>
  );
};

export default BackerNotificationSettingsForm;
