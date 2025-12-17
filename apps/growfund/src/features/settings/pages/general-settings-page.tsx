import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { ComboBoxField } from '@/components/form/combobox-field';
import { EditorField } from '@/components/form/editor-field';
import { TextField } from '@/components/form/text-field';
import GeneralSettingsDonationFormOptionsFallback from '@/components/pro-fallbacks/settings/general/donation-form-options-fallback';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  GeneralSettingsSchema,
  type GeneralSettingsForm,
} from '@/features/settings/schemas/settings';
import { useWordPressPagesQuery } from '@/features/settings/services/general';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { type Option } from '@/types';
import { countriesAsOptions, statesAsOptions } from '@/utils/countries';

const GeneralSettingsPage = () => {
  const { appConfig, isDonationMode } = useAppConfig();
  const form = useForm<GeneralSettingsForm>({
    resolver: zodResolver(GeneralSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, appConfig[AppConfigKeys.General] ?? {});
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<GeneralSettingsForm>();

  const wordpressPagesQuery = useWordPressPagesQuery();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.General, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const countryCode = useWatch({ control: form.control, name: 'country' });

  const pagesOptions = useMemo(() => {
    if (!wordpressPagesQuery.data) {
      return [] as Option<string>[];
    }
    return wordpressPagesQuery.data.map((page) => ({ label: page.name, value: page.id }));
  }, [wordpressPagesQuery.data]);

  const GeneralSettingsDonationFormOptions = registry.get('GeneralSettingsDonationFormOptions');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('Basic Information', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Specify the location for donation collection and processing.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <ComboBoxField
              control={form.control}
              name="country"
              options={countriesAsOptions({ with_flag: true })}
              label={__('Base Country', 'growfund')}
              placeholder={__('Select a country', 'growfund')}
            />
            <ComboBoxField
              control={form.control}
              name="state"
              options={statesAsOptions(countryCode)}
              label={__('State', 'growfund')}
              placeholder={__('Select a state', 'growfund')}
            />
          </CardContent>
        </Card>

        {/* Organization Info */}
        <Card>
          <CardHeader>
            <CardTitle>{__('Organization Information', 'growfund')}</CardTitle>
            <CardDescription>
              {__(
                'This information will be used in communications and displayed in relevant sections..',
                'growfund',
              )}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <TextField
              control={form.control}
              name="organization.name"
              label={__('Organization Name', 'growfund')}
              placeholder={__('e.g. Your Organization', 'growfund')}
            />
            <TextField
              control={form.control}
              name="organization.location"
              label={__('Location', 'growfund')}
              placeholder={__('e.g. 1‌000 West Maude Avenue, Sunnyvale, CA 94085.', 'growfund')}
            />
            <TextField
              control={form.control}
              name="organization.contact_email"
              label={__('Contact Email', 'growfund')}
              placeholder={__('e.g. support@yourorg.com', 'growfund')}
            />
          </CardContent>
        </Card>

        {/* Page options */}
        <Card>
          <CardHeader>
            <CardTitle>{__('Page Options', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Select the pages for different donation stages.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <ComboBoxField
              control={form.control}
              name="registration_page"
              options={pagesOptions}
              label={
                isDonationMode
                  ? __('Donor Registration Page', 'growfund')
                  : __('Backer Registration Page', 'growfund')
              }
              placeholder={__('Select a page', 'growfund')}
            />
            <ComboBoxField
              control={form.control}
              name="login_page"
              options={pagesOptions}
              label={__('Login Page', 'growfund')}
              placeholder={__('Select a page', 'growfund')}
            />
            <ComboBoxField
              control={form.control}
              name="privacy_policy_page"
              options={pagesOptions}
              label={__('Privacy Policy Page', 'growfund')}
              placeholder={__('Select a page', 'growfund')}
            />
            <ComboBoxField
              control={form.control}
              name="terms_and_conditions_page"
              options={pagesOptions}
              label={__('Terms & Conditions Page', 'growfund')}
              placeholder={__('Select a page', 'growfund')}
            />
          </CardContent>
        </Card>

        {/* Donation form options */}
        {isDonationMode && (
          <ElementWrapper fallback={<GeneralSettingsDonationFormOptionsFallback />}>
            {GeneralSettingsDonationFormOptions && <GeneralSettingsDonationFormOptions />}
          </ElementWrapper>
        )}

        <Card>
          <CardHeader>
            <CardTitle>{__('Checkout Consent', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Specify the terms and conditions for checkout consent.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <EditorField
              control={form.control}
              name="tnc_text"
              label={__('Text', 'growfund')}
              placeholder={__('Enter the terms and conditions text', 'growfund')}
              rows={10}
              shortCodes={[
                {
                  label: __('Privacy Policy', 'growfund'),
                  value: '{privacy_policy_page}',
                },
                {
                  label: __('Terms & Conditions', 'growfund'),
                  value: '{terms_and_conditions_page}',
                },
              ]}
            />
          </CardContent>
        </Card>
      </div>
    </Form>
  );
};

export default GeneralSettingsPage;
