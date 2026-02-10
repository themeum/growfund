import { ExclamationTriangleIcon, GearIcon, ReloadIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { Outlet } from 'react-router';
import { toast } from 'sonner';

import { Container } from '@/components/layouts/container';
import { Page, PageContent, PageHeader } from '@/components/layouts/page';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { RouteConfig } from '@/config/route-config';
import { useConsentDialog } from '@/features/campaigns/contexts/consent-dialog-context';
import SettingsSidebar from '@/features/settings/components/settings-sidebar';
import {
  SettingsPage,
  SettingsPageContent,
  SettingsPageHeader,
} from '@/features/settings/components/ui/settings-page';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { type AppConfig } from '@/features/settings/schemas/settings';
import { useCurrentPath } from '@/hooks/use-current-path';
import { useStoreAppConfigMutation } from '@/services/app-config';
import { isDefined } from '@/utils';
import { preProcessSettingsData } from '@/utils/settings';

const settingsPageHeaders = {
  [RouteConfig.GeneralSettings.template]: __('General', 'growfund'),
  [RouteConfig.CampaignSettings.template]: __('Campaign', 'growfund'),
  [RouteConfig.UserAndPermissionsSettings.template]: __('User & Permissions', 'growfund'),
  [RouteConfig.PaymentSettings.template]: __('Payment', 'growfund'),
  [RouteConfig.PdfReceiptSettings.template]: __('Receipt', 'growfund'),
  [RouteConfig.EmailAndNotificationsSettings.template]: __('Email & Notifications', 'growfund'),
  [RouteConfig.SecuritySettings.template]: __('Security & Authentication', 'growfund'),
  [RouteConfig.IntegrationsSettings.template]: __('Integrations', 'growfund'),
  [RouteConfig.BrandingSettings.template]: __('Branding', 'growfund'),
  [RouteConfig.AdvancedSettings.template]: __('Advanced', 'growfund'),
  [RouteConfig.LicenseSettings.template]: __('License', 'growfund'),
  [RouteConfig.PagesSettings.template]: __('Pages', 'growfund'),
} as const;

const SettingsLayoutContent = () => {
  const currentPath = useCurrentPath();
  const { getCurrentForm, getCurrentKey, isCurrentFormDirty } = useSettingsContext();
  const storeAppConfigMutation = useStoreAppConfigMutation();

  const form = getCurrentForm();
  const key = getCurrentKey();

  const { openDialog } = useConsentDialog();

  const resetSettingsHandler = async () => {
    const currentKey = getCurrentKey();
    const currentForm = getCurrentForm();

    if (!currentKey || !currentForm) {
      return;
    }

    let defaultOptions: AppConfig | null = null;

    try {
      defaultOptions =
        (await import('../../../../../../wordpress/wp-content/plugins/growfund/resources/data/default-options.json')) as unknown as AppConfig | null;
    } catch {
      defaultOptions = null;
    }

    if (!defaultOptions) {
      return;
    }

    if (!(currentKey in defaultOptions)) {
      return;
    }

    const defaultValue = defaultOptions[currentKey];

    if (defaultValue && typeof defaultValue === 'object' && !Array.isArray(defaultValue)) {
      storeAppConfigMutation.mutate(
        {
          key: currentKey,
          data: JSON.stringify(defaultValue),
        },
        {
          onSuccess() {
            toast.success(__('Settings reset to default', 'growfund'));
          },
        },
      );
    }
  };

  // Exclude any key if you want to remove the reset button from the setting page
  const showResetSettingsButton = useMemo(
    () =>
      [
        AppConfigKeys.General,
        AppConfigKeys.Campaign,
        AppConfigKeys.UserAndPermissions,
        AppConfigKeys.Payment,
        AppConfigKeys.Receipt,
        AppConfigKeys.EmailAndNotifications,
        AppConfigKeys.Security,
        AppConfigKeys.Branding,
        AppConfigKeys.Advanced,
      ].includes(key as unknown as AppConfigKeys),
    [key],
  );

  return (
    <Page>
      <PageHeader
        name={__('Settings', 'growfund')}
        icon={<GearIcon className="growfund-size-6" />}
        action={
          currentPath !== RouteConfig.LicenseSettings.template && (
            <div className="growfund-flex growfund-items-center growfund-gap-3">
              <Button
                variant="outline"
                disabled={storeAppConfigMutation.isPending || !isCurrentFormDirty}
                onClick={() => {
                  form?.reset();
                }}
              >
                {__('Discard', 'growfund')}
              </Button>
              <Button
                onClick={() => {
                  if (!form || !key) return;

                  void form.handleSubmit(
                    (values) => {
                      storeAppConfigMutation.mutate(
                        {
                          key,
                          data: JSON.stringify(preProcessSettingsData(values, key)),
                        },
                        {
                          onSuccess(data) {
                            toast.success((data as unknown as { message: string }).message);
                            form.reset(form.getValues());
                          },
                        },
                      );
                    },
                    (errors) => {
                      console.error(errors);
                    },
                  )();
                }}
                disabled={storeAppConfigMutation.isPending || !isCurrentFormDirty}
                loading={storeAppConfigMutation.isPending}
              >
                {__('Save', 'growfund')}
              </Button>
            </div>
          )
        }
      />
      <PageContent>
        <Container className="growfund-mt-6 growfund-grid growfund-grid-cols-[212px_auto] growfund-gap-6">
          <SettingsSidebar className="growfund-h-fit" />
          <div className="growfund-h-full">
            <SettingsPage>
              {isDefined(currentPath) && (
                <SettingsPageHeader className="growfund-flex growfund-items-center growfund-justify-between">
                  {settingsPageHeaders[currentPath as keyof typeof settingsPageHeaders]}
                  {showResetSettingsButton && (
                    <Button
                      variant="ghost"
                      onClick={() => {
                        openDialog({
                          title: __('Reset to Default Settings', 'growfund'),
                          content: (
                            <Alert variant="warning">
                              <AlertTitle className="growfund-flex growfund-items-center growfund-gap-2">
                                <ExclamationTriangleIcon />
                                {__('WARNING', 'growfund')}
                              </AlertTitle>
                              <AlertDescription>
                                {__(
                                  'This will overwrite all customized settings of this section and reset them to default. Proceed with caution.',
                                  'growfund',
                                )}
                              </AlertDescription>
                            </Alert>
                          ),
                          confirmText: __('Reset', 'growfund'),
                          declineText: __('Cancel', 'growfund'),
                          onConfirm: async (closeDialog) => {
                            await resetSettingsHandler();
                            closeDialog();
                          },
                        });
                      }}
                    >
                      <ReloadIcon />
                      {__('Reset to Default', 'growfund')}
                    </Button>
                  )}
                </SettingsPageHeader>
              )}
              <SettingsPageContent>
                <Outlet />
              </SettingsPageContent>
            </SettingsPage>
          </div>
        </Container>
      </PageContent>
    </Page>
  );
};

export default SettingsLayoutContent;
