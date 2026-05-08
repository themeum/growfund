import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { ComboBoxField } from '@/components/form/combobox-field';
import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import PaymentSettingsDigitalWalletFallback from '@/components/pro-fallbacks/settings/payment/digital-wallet-fallback';
import PaymentSettingsGuestCheckoutFallback from '@/components/pro-fallbacks/settings/payment/guest-checkout-fallback';
import PaymentSettingsPlatformFeeFallback from '@/components/pro-fallbacks/settings/payment/platform-fee-fallback';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { growfundConfig } from '@/config/growfund';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import PaymentMethodsCard from '@/features/settings/features/payments/components/payment-methods-card';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  PaymentSettingsFormSchema,
  type PaymentSettingsForm,
} from '@/features/settings/schemas/settings';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';
import { currenciesAsOptions } from '@/utils/currencies';

const PaymentSettingsPage = () => {
  const { appConfig, isDonationMode } = useAppConfig();

  const form = useForm<PaymentSettingsForm>({
    resolver: zodResolver(PaymentSettingsFormSchema),
  });

  useEffect(() => {
    const paymentSettings = appConfig[AppConfigKeys.Payment];
    form.reset.call(null, {
      ...getDefaults(PaymentSettingsFormSchema._def.schema),
      ...paymentSettings,
    });
  }, [appConfig, form.reset]);

  const { registerForm, isCurrentFormDirty } = useSettingsContext<PaymentSettingsForm>();

  useEffect(() => {
    const cleanup = registerForm(AppConfigKeys.Payment, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useUpdateDirtyState(form);
  useRouteBlockerGuard({ isDirty: isCurrentFormDirty });

  const eCommerceEngine = useWatch({ control: form.control, name: 'e_commerce_engine' });

  const PaymentSettingsPlatformFee = registry.get('PaymentSettingsPlatformFee');
  const PaymentSettingsDigitalWallet = registry.get('PaymentSettingsDigitalWallet');
  const PaymentSettingsGuestCheckout = registry.get('PaymentSettingsGuestCheckout');

  return (
    <Form {...form}>
      <div className="growfund-grid growfund-gap-4">
        <Card>
          <CardHeader>
            <CardTitle>{__('Monetization', 'growfund')}</CardTitle>
            <CardDescription>
              {__(
                'Select the platform or engine you want to use for collecting donations.',
                'growfund',
              )}
            </CardDescription>
          </CardHeader>
          <CardContent className="growfund-space-y-4">
            <SelectField
              control={form.control}
              name="e_commerce_engine"
              options={[
                { label: __('WooCommerce', 'growfund'), value: 'woo-commerce' },
                { label: __('Growfund Native', 'growfund'), value: 'native' },
              ]}
              label={__('E-commerce Engine', 'growfund')}
              placeholder={__('Select an e-commerce engine', 'growfund')}
              disabled={!growfundConfig.is_woocommerce_installed}
            />
          </CardContent>
        </Card>

        {eCommerceEngine === 'native' && (
          <>
            {/* Payment Methods */}
            <PaymentMethodsCard />

            {/* Currency and Formatting */}
            <Card>
              <CardHeader>
                <CardTitle>{__('Currency & Formatting', 'growfund')}</CardTitle>
                <CardDescription>
                  {__(
                    'Specify the currency settings for donation collection and processing.',
                    'growfund',
                  )}
                </CardDescription>
              </CardHeader>
              <CardContent className="growfund-space-y-4">
                <ComboBoxField
                  control={form.control}
                  name="currency"
                  options={currenciesAsOptions()}
                  label={__('Currency', 'growfund')}
                  placeholder={__('Select a currency', 'growfund')}
                />
                <SelectField
                  control={form.control}
                  name="currency_position"
                  options={[
                    { label: __('Before', 'growfund'), value: 'before' },
                    { label: __('After', 'growfund'), value: 'after' },
                  ]}
                  label={__('Currency Position', 'growfund')}
                  placeholder={__('Select a currency position', 'growfund')}
                />
                <SelectField
                  control={form.control}
                  name="decimal_separator"
                  options={[
                    { label: __('. (dot)', 'growfund'), value: '.' },
                    { label: __(', (comma)', 'growfund'), value: ',' },
                    { label: __('Space', 'growfund'), value: ' ' },
                  ]}
                  label={__('Decimal Separator', 'growfund')}
                  placeholder={__('Select a decimal separator', 'growfund')}
                />
                <SelectField
                  control={form.control}
                  name="thousand_separator"
                  options={[
                    { label: __('. (dot)', 'growfund'), value: '.' },
                    { label: __(', (comma)', 'growfund'), value: ',' },
                    { label: __('Space', 'growfund'), value: ' ' },
                  ]}
                  label={__('Thousand Separator', 'growfund')}
                  placeholder={__('Select a thousand separator', 'growfund')}
                />
                <TextField
                  control={form.control}
                  type="number"
                  name="decimal_places"
                  label={__('Number of Decimals', 'growfund')}
                  placeholder={__('Select a decimal places', 'growfund')}
                />
              </CardContent>
            </Card>
          </>
        )}

        {/* Platform Fee */}
        <ElementWrapper fallback={<PaymentSettingsPlatformFeeFallback />}>
          {PaymentSettingsPlatformFee && <PaymentSettingsPlatformFee />}
        </ElementWrapper>

        {/* Wallet settings */}
        <ElementWrapper fallback={<PaymentSettingsDigitalWalletFallback />}>
          {PaymentSettingsDigitalWallet && <PaymentSettingsDigitalWallet />}
        </ElementWrapper>

        {isDonationMode && (
          <ElementWrapper fallback={<PaymentSettingsGuestCheckoutFallback />}>
            {PaymentSettingsGuestCheckout && <PaymentSettingsGuestCheckout />}
          </ElementWrapper>
        )}
      </div>
    </Form>
  );
};

export default PaymentSettingsPage;
