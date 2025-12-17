import { zodResolver } from '@hookform/resolvers/zod';
import { EnvelopeClosedIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { CheckCircle, Edit, HeartHandshake, Paintbrush, UserCog, Wrench } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router';

import ElementWrapper from '@/components/element-wrapper';
import FundraiserEmailFieldsFallback from '@/components/pro-fallbacks/settings/email-notifications/fundraiser-email-fields-fallback';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { RouteConfig } from '@/config/route-config';
import { useAppConfig } from '@/contexts/app-config';
import EmailConfigDialog from '@/features/settings/components/dialogs/email-config-dialog';
import AdminEmailSwitchFields from '@/features/settings/components/ui/switch-fields/admin-email-switch-fields';
import BackerEmailSwitchFields from '@/features/settings/components/ui/switch-fields/backer-email-switch-fields';
import DonorEmailSwitchFields from '@/features/settings/components/ui/switch-fields/donor-email-switch-fields';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import { EmailSettingsSchema, type EmailSettingsForm } from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';
import { type Option } from '@/types';
import { isDefined } from '@/utils';

const isMailServerConfigured = (data: Option<string | null>[]) => {
  return data.some((item) => !!item.value);
};

const EmailNotificationSettingsPage = () => {
  const [openMailConfig, setOpenMailConfig] = useState(false);
  const navigate = useNavigate();
  const { appConfig, isDonationMode } = useAppConfig();

  const form = useForm<EmailSettingsForm>({
    resolver: zodResolver(EmailSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(EmailSettingsSchema),
      ...(appConfig[AppConfigKeys.EmailAndNotifications] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<EmailSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.EmailAndNotifications, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const mailValue = useWatch({ control: form.control, name: 'mail' });

  const mailer = mailValue?.mailer ?? null;
  const from_email = mailValue?.from_email ?? null;
  const from_name = mailValue?.from_name ?? null;

  const mailerMap = new Map<'smtp' | 'php-mail', string>([
    ['smtp', __('SMTP', 'growfund')],
    ['php-mail', __('PHP Mail', 'growfund')],
  ]);
  const encryptionMap = new Map<'none' | 'ssl' | 'tls', string>([
    ['none', __('None', 'growfund')],
    ['ssl', __('SSL/TLS', 'growfund')],
    ['tls', __('STARTTLS', 'growfund')],
  ]);

  const configDisplayValues = [
    {
      label: __('Mailer:', 'growfund'),
      value: mailer ? mailerMap.get(mailer) : null,
    },
    {
      label: __('Host:', 'growfund'),
      value: mailValue?.mailer === 'smtp' ? mailValue.host : null,
    },
    {
      label: __('Port:', 'growfund'),
      value: mailValue?.mailer === 'smtp' ? mailValue.port : null,
    },
    {
      label: __('Encryption:', 'growfund'),
      value: mailValue?.mailer === 'smtp' ? encryptionMap.get(mailValue.encryption) : null,
    },
  ] as Option<string | null>[];

  const isConfigured = isMailServerConfigured(configDisplayValues);

  const FundraiserEmailFields = registry.get('FundraiserEmailFields');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader className="growfund-space-y-0">
            <div className="growfund-flex growfund-items-center growfund-justify-between">
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <Paintbrush className="growfund-size-4 growfund-text-icon-primary growfund-shrink-0" />
                <CardTitle>{__('Default Template', 'growfund')}</CardTitle>
              </div>
              <Button
                variant="outline"
                onClick={() => navigate(RouteConfig.EmailNotificationTemplate.buildLink())}
              >
                {__('Edit', 'growfund')}
              </Button>
            </div>
            <CardDescription>
              {__(
                'Configure logo, colors, sender email, and more for default system emails.',
                'growfund',
              )}
            </CardDescription>
          </CardHeader>
        </Card>

        {/* Mail server settings */}
        <Card>
          <CardHeader className="growfund-flex-row growfund-items-center">
            <div className="growfund-space-y-3">
              <CardTitle className="growfund-flex growfund-items-center growfund-gap-2">
                <EnvelopeClosedIcon className="growfund-size-4 growfund-text-icon-primary growfund-shrink-0" />
                {__('Mail server configuration', 'growfund')}
                {isConfigured && (
                  <Badge>
                    <CheckCircle className="growfund-size-4" strokeWidth={1} />
                    {__('Configured', 'growfund')}
                  </Badge>
                )}
              </CardTitle>
              <CardDescription>
                {__('Configure your email server settings here.', 'growfund')}
              </CardDescription>
            </div>
            <EmailConfigDialog
              open={openMailConfig}
              onOpenChange={setOpenMailConfig}
              defaultValues={mailValue ?? undefined}
              onValueChange={(values) => {
                form.setValue('mail', values, {
                  shouldDirty: true,
                });
              }}
            >
              {isConfigured ? (
                <Button variant="outline" size="icon" className="growfund-size-8 growfund-ms-auto">
                  <Edit />
                </Button>
              ) : (
                <Button variant="outline" size="sm" className="growfund-ms-auto">
                  <Wrench />
                  {__('Configure', 'growfund')}
                </Button>
              )}
            </EmailConfigDialog>
          </CardHeader>
          {isConfigured && (
            <CardContent className="growfund-space-y-4">
              <div className="growfund-bg-background-surface-secondary growfund-rounded-md growfund-p-4 growfund-space-y-2">
                <div className="growfund-flex growfund-flex-col growfund-gap-2 growfund-flex-wrap">
                  {configDisplayValues
                    .filter((value) => isDefined(value.value) && !!value.value)
                    .map((data, index) => {
                      return (
                        <div
                          className="growfund-flex growfund-items-center growfund-gap-1 growfund-typo-small growfund-text-fg-primary"
                          key={index}
                        >
                          <span className="growfund-text-fg-subdued">{data.label}</span>
                          <span>{data.value}</span>
                        </div>
                      );
                    })}
                </div>
                <div className="growfund-flex growfund-items-center growfund-gap-4">
                  <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-typo-small growfund-text-fg-primary">
                    <span className="growfund-text-fg-subdued">
                      {__('From Email:', 'growfund')}
                    </span>
                    <span>{from_email}</span>
                  </div>
                  <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-typo-small growfund-text-fg-primary">
                    <span className="growfund-text-fg-subdued">{__('From Name:', 'growfund')}</span>
                    <span>{from_name}</span>
                  </div>
                </div>
              </div>
            </CardContent>
          )}
        </Card>

        {/* Admin Emails */}
        <Card>
          <CardHeader>
            <div className="growfund-flex growfund-items-center growfund-justify-between">
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <UserCog className="growfund-size-4 growfund-text-icon-primary growfund-shrink-0" />
                <CardTitle>{__('Admin Emails', 'growfund')}</CardTitle>
              </div>
            </div>
            <CardDescription>{__('Manage admin email settings here.', 'growfund')}</CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-2">
            <AdminEmailSwitchFields />
          </CardContent>
        </Card>

        {/* Fundraiser Emails */}
        <ElementWrapper fallback={<FundraiserEmailFieldsFallback />}>
          {FundraiserEmailFields && <FundraiserEmailFields />}
        </ElementWrapper>

        {/* Donor Emails */}
        {isDonationMode && (
          <Card>
            <CardHeader>
              <div className="growfund-flex growfund-items-center growfund-justify-between">
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <HeartHandshake className="growfund-size-4 growfund-text-icon-primary growfund-shrink-0" />
                  <CardTitle>{__('Donor Emails', 'growfund')}</CardTitle>
                </div>
              </div>
              <CardDescription>
                {__('Manage donor email settings here.', 'growfund')}
              </CardDescription>
            </CardHeader>
            <CardContent className="growfund-space-y-2">
              <DonorEmailSwitchFields />
            </CardContent>
          </Card>
        )}

        {/* Backer Emails */}
        {!isDonationMode && (
          <Card>
            <CardHeader>
              <div className="growfund-flex growfund-items-center growfund-justify-between">
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <HeartHandshake className="growfund-w-4 growfund-h-4 growfund-text-icon-primary growfund-shrink-0" />
                  <CardTitle>{__('Backer Emails', 'growfund')}</CardTitle>
                </div>
              </div>
              <CardDescription>
                {__('Manage backer email settings here.', 'growfund')}
              </CardDescription>
            </CardHeader>
            <CardContent className="growfund-space-y-2">
              <BackerEmailSwitchFields />
            </CardContent>
          </Card>
        )}
      </div>
    </Form>
  );
};

export default EmailNotificationSettingsPage;
