import { zodResolver } from '@hookform/resolvers/zod';
import { __, sprintf } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import { SwitchField } from '@/components/form/switch-field';
import { TextField } from '@/components/form/text-field';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
    type AdvancedSettingsForm,
    AdvancedSettingsSchema,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { trim } from '@/utils';

const AdvancedSettingsPage = () => {
  const { appConfig } = useAppConfig();
  const form = useForm<AdvancedSettingsForm>({
    resolver: zodResolver(AdvancedSettingsSchema),
  });

  useEffect(() => {
    if (appConfig[AppConfigKeys.Advanced]) {
      form.reset.call(null, appConfig[AppConfigKeys.Advanced]);
    }
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<AdvancedSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Advanced, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const permalink = useWatch({ control: form.control, name: 'campaign_permalink' });
  const permalinkUrl = useMemo(() => {
    if (!permalink) return;
    const baseUrl = window.location.origin;
    return sprintf('%s/%s/slug-of-the-campaign', baseUrl, trim(permalink, '/'));
  }, [permalink]);

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('System Configuration', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Control system settings and performance options.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <TextField
              control={form.control}
              name="campaign_permalink"
              label={__('Campaign Permalink', 'growfund')}
              placeholder={__('e.g. campaigns', 'growfund')}
              description={permalinkUrl}
            />
          </CardContent>
        </Card>

        {/* Data management settings */}
        <Card>
          <CardHeader>
            <CardTitle>{__('Data Management', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Configure data handling, retention, and cleanup options.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <SwitchField
              control={form.control}
              name="erase_data_upon_uninstallation"
              label={__('Erase Data Upon Uninstallation', 'growfund')}
              description={__('This removes all data when the plugin is uninstalled.', 'growfund')}
            />
          </CardContent>
        </Card>
      </div>
    </Form>
  );
};

export default AdvancedSettingsPage;
