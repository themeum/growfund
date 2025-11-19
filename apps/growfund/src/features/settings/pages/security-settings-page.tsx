import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import FeatureGuard from '@/components/feature-guard';
import { ProSwitchInput } from '@/components/pro-fallbacks/form/pro-switch-input';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
    SecuritySettingsSchema,
    type SecuritySettingsForm,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';

const SecuritySettingsPage = () => {
  const { appConfig } = useAppConfig();
  const form = useForm<SecuritySettingsForm>({
    resolver: zodResolver(SecuritySettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(SecuritySettingsSchema._def.schema),
      ...(appConfig[AppConfigKeys.Security] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<SecuritySettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Security, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const SecuritySettingsEmailVerificationSwitchField = registry.get(
    'SecuritySettingsEmailVerificationSwitchField',
  );

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('Access Control', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Manage authentication and account security settings.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <FeatureGuard
              feature="settings.security.email_verification"
              fallback={
                <ProSwitchInput
                  label={__('Email Verification', 'growfund')}
                  description={__(
                    'Ensure user account security by requiring email verification for account activation.',
                    'growfund',
                  )}
                  showProBadge
                />
              }
            >
              {SecuritySettingsEmailVerificationSwitchField && (
                <SecuritySettingsEmailVerificationSwitchField />
              )}
            </FeatureGuard>
          </CardContent>
        </Card>
      </div>
    </Form>
  );
};

export default SecuritySettingsPage;
