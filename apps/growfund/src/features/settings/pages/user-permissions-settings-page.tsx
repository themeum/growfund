import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import FeatureGuard from '@/components/feature-guard';
import UserPermissionsAnonymousContributionsFallback from '@/components/pro-fallbacks/settings/user-permissions/anonymous-contributions-fallback';
import UserPermissionsContributorCommentsFallback from '@/components/pro-fallbacks/settings/user-permissions/contributor-comments-fallback';
import UserPermissionsFundraiserSettingsFallback from '@/components/pro-fallbacks/settings/user-permissions/fundraiser-settings-fallback';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
    type UserPermissionsSettingsForm,
    UserPermissionsSettingsSchema,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';

const UserPermissionsSettingsPage = () => {
  const { appConfig, isDonationMode } = useAppConfig();
  const form = useForm<UserPermissionsSettingsForm>({
    resolver: zodResolver(UserPermissionsSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(UserPermissionsSettingsSchema),
      ...(appConfig[AppConfigKeys.UserAndPermissions] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<UserPermissionsSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.UserAndPermissions, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const UserPermissionsFundraiserSettings = registry.get('UserPermissionsFundraiserSettings');
  const UserPermissionsAnonymousContributions = registry.get(
    'UserPermissionsAnonymousContributions',
  );
  const UserPermissionsContributorComments = registry.get('UserPermissionsContributorComments');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <FeatureGuard
          feature="settings.user_permissions.fundraisers"
          fallback={<UserPermissionsFundraiserSettingsFallback />}
        >
          {UserPermissionsFundraiserSettings && <UserPermissionsFundraiserSettings />}
        </FeatureGuard>

        {/* Donor/Backer permissions */}
        <Card>
          <CardHeader>
            <CardTitle>
              {isDonationMode ? __('Donor', 'growfund') : __('Backer', 'growfund')}
            </CardTitle>
            <CardDescription>
              {isDonationMode
                ? __('Manage donor permissions and controls.', 'growfund')
                : __('Manage backer permissions and controls.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            {isDonationMode && (
              <FeatureGuard
                feature="settings.user_permissions.anonymous_contributions"
                fallback={<UserPermissionsAnonymousContributionsFallback />}
              >
                {UserPermissionsAnonymousContributions && <UserPermissionsAnonymousContributions />}
              </FeatureGuard>
            )}
            <FeatureGuard
              feature="settings.user_permissions.contributor_comments"
              fallback={<UserPermissionsContributorCommentsFallback />}
            >
              {UserPermissionsContributorComments && <UserPermissionsContributorComments />}
            </FeatureGuard>
          </CardContent>
        </Card>
      </div>
    </Form>
  );
};

export default UserPermissionsSettingsPage;
