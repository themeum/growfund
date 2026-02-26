import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { MultiSelectField } from '@/components/form/multiselect-field';
import { SelectField } from '@/components/form/select-field';
import { SwitchField } from '@/components/form/switch-field';
import { ProRadioInput } from '@/components/pro-fallbacks/form/pro-radio-input';
import CampaignSettingsEnableCommentsFallback from '@/components/pro-fallbacks/settings/campaign/enable-comments-fallback';
import CampaignSettingsFundFallback from '@/components/pro-fallbacks/settings/campaign/fund-fallback';
import CampaignSettingsTributeFallback from '@/components/pro-fallbacks/settings/campaign/tribute-fallback';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  CampaignSettingsSchema,
  type CampaignSettingsForm,
  type SocialShareProviders,
} from '@/features/settings/schemas/settings';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';
import { type Option } from '@/types';

const socialShares: Option<SocialShareProviders>[] = [
  {
    label: __('Facebook', 'growfund'),
    value: 'facebook',
  },
  {
    label: __('X', 'growfund'),
    value: 'x',
  },
  {
    label: __('LinkedIn', 'growfund'),
    value: 'linkedin',
  },
  {
    label: __('WhatsApp', 'growfund'),
    value: 'whatsapp',
  },
  {
    label: __('Telegram', 'growfund'),
    value: 'telegram',
  },
];

const CampaignSettingsPage = () => {
  const { appConfig, isDonationMode } = useAppConfig();

  const form = useForm<CampaignSettingsForm>({
    resolver: zodResolver(CampaignSettingsSchema),
  });

  useEffect(() => {
    form.reset.call(null, {
      ...getDefaults(CampaignSettingsSchema),
      ...(appConfig[AppConfigKeys.Campaign] ?? {}),
    });
  }, [appConfig, form.reset]);

  const { registerForm } = useSettingsContext<CampaignSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Campaign, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);

  const CampaignsSettingsEnableComments = registry.get('CampaignsSettingsEnableComments');
  const CampaignSettingsTribute = registry.get('CampaignSettingsTribute');
  const CampaignSettingsFund = registry.get('CampaignSettingsFund');
  const CampaignsSettingsCampaignVisibility = registry.get('CampaignsSettingsCampaignVisibility');

  const displayContributorListPublicly = useWatch({
    control: form.control,
    name: 'display_contributor_list_publicly',
  });

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('Campaign Options', 'growfund')}</CardTitle>
            <CardDescription>
              {__('Control how campaign details are displayed to donors.', 'growfund')}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <SwitchField
              control={form.control}
              name="is_login_required_to_view_campaign_detail"
              label={__('Require Login to View Campaign Details', 'growfund')}
              description={__(
                'If enabled, only logged in users will be able to view campaign details.',
                'growfund',
              )}
            />

            {isDonationMode && (
              <div className="growfund-space-y-2">
                <SwitchField
                  control={form.control}
                  name="display_contributor_list_publicly"
                  label={__('Display Donor List Publicly', 'growfund')}
                  description={__(
                    'When enabled, names of donors will be visible to anyone viewing the campaign.',
                    'growfund',
                  )}
                />

                {displayContributorListPublicly && (
                  <>
                    <SelectField
                      control={form.control}
                      name="display_contributor_option"
                      options={[
                        { label: __('Show donation amounts', 'growfund'), value: 'show-amount' },
                        { label: __('Show only names', 'growfund'), value: 'show-name' },
                        {
                          label: __('Show names & amounts', 'growfund'),
                          value: 'show-amount-and-name',
                        },
                      ]}
                      label={__('Display Option', 'growfund')}
                    />
                    <div className="growfund-flex growfund-items-center growfund-gap-3">
                      <SelectField
                        control={form.control}
                        name="display_contributor_option_order_by"
                        options={[
                          {
                            label: __('Top & Recent (Recommended)', 'growfund'),
                            value: 'top-and-recent',
                          },
                          { label: __('Recent only', 'growfund'), value: 'recent-only' },
                          { label: __('Top only', 'growfund'), value: 'top-only' },
                        ]}
                        label={__('Display by', 'growfund')}
                      />
                      <SelectField
                        control={form.control}
                        name="display_contributor_option_limit"
                        options={[
                          { label: __('20', 'growfund'), value: '20' },
                          { label: __('50', 'growfund'), value: '50' },
                          { label: __('100', 'growfund'), value: '100' },
                          { label: __('All', 'growfund'), value: 'all' },
                        ]}
                        label={__('Max length', 'growfund')}
                      />
                    </div>
                  </>
                )}
              </div>
            )}

            <ElementWrapper
              fallback={
                <ProRadioInput
                  label={__('Campaign Updates Visibility', 'growfund')}
                  options={[
                    __('Visible to Everyone', 'growfund'),
                    isDonationMode
                      ? __('Visible to Donors Only', 'growfund')
                      : __('Visible to Backers Only', 'growfund'),
                    __('Visible to Logged-in Users Only', 'growfund'),
                  ]}
                  showProBadge
                />
              }
            >
              {CampaignsSettingsCampaignVisibility && <CampaignsSettingsCampaignVisibility />}
            </ElementWrapper>

            <MultiSelectField
              control={form.control}
              name="social_shares"
              options={socialShares}
              label={__('Social Sharing Options', 'growfund')}
              placeholder={__('Select Social Sharing Options', 'growfund')}
            />
          </CardContent>
        </Card>

        <ElementWrapper fallback={<CampaignSettingsEnableCommentsFallback />}>
          {CampaignsSettingsEnableComments && <CampaignsSettingsEnableComments />}
        </ElementWrapper>

        {isDonationMode && (
          <>
            <ElementWrapper fallback={<CampaignSettingsTributeFallback />}>
              {CampaignSettingsTribute && <CampaignSettingsTribute />}
            </ElementWrapper>
            <ElementWrapper fallback={<CampaignSettingsFundFallback />}>
              {CampaignSettingsFund && <CampaignSettingsFund />}
            </ElementWrapper>
          </>
        )}
      </div>
    </Form>
  );
};

export default CampaignSettingsPage;
