// @todo: All commented codes will be uncommented when the feature is ready

import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect, useMemo } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
// import { CheckboxField } from '@/components/form/checkbox-field';
import { ComboBoxField } from '@/components/form/combobox-field';
import { SelectField } from '@/components/form/select-field';
// import { SwitchField } from '@/components/form/switch-field';
import { TextField } from '@/components/form/text-field';
// import PaymentSettingsAdminCommissionFallback from '@/components/pro-fallbacks/settings/payment/admin-commission-fallback';
import PaymentSettingsGuestCheckoutFallback from '@/components/pro-fallbacks/settings/payment/guest-checkout-fallback';
// import { Box, BoxContent } from '@/components/ui/box';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { growfundConfig } from '@/config/growfund';
import { useAppConfig } from '@/contexts/app-config';
import { AppConfigKeys, useSettingsContext } from '@/features/settings/context/settings-context';
import PaymentMethodsCard from '@/features/settings/features/payments/components/payment-methods-card';
import { useUpdateDirtyState } from '@/features/settings/hooks/use-update-dirty-state';
import {
  PaymentSettingsSchema,
  type PaymentSettingsForm,
} from '@/features/settings/schemas/settings';
import { useWordPressPagesQuery } from '@/features/settings/services/general';
// import { useCurrency } from '@/hooks/use-currency';
import { useRouteBlockerGuard } from '@/hooks/use-route-blocker-guard';
import { registry } from '@/lib/registry';
import { getDefaults } from '@/lib/zod';
import { currenciesAsOptions } from '@/utils/currencies';

const PaymentSettingsPage = () => {
  const { appConfig, isDonationMode } = useAppConfig();
  // const { toCurrency } = useCurrency();

  const form = useForm<PaymentSettingsForm>({
    resolver: zodResolver(PaymentSettingsSchema),
  });

  useEffect(() => {
    const paymentSettings = appConfig[AppConfigKeys.Payment];
    form.reset.call(null, { ...getDefaults(PaymentSettingsSchema), ...paymentSettings });
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

  // const enableWallet = useWatch({ control: form.control, name: 'enable_wallet' });
  const eCommerceEngine = useWatch({ control: form.control, name: 'e_commerce_engine' });

  // const limitOnRevenueWithdrawal = useWatch({
  //   control: form.control,
  //   name: 'put_limit_on_revenue_withdrawal',
  // });
  // const revenueWithdrawalType = useWatch({
  //   control: form.control,
  //   name: 'revenue_withdrawal_type',
  // });
  const wordpressPagesQuery = useWordPressPagesQuery();
  const pageOptions = useMemo(() => {
    if (!wordpressPagesQuery.data) {
      return [];
    }
    return wordpressPagesQuery.data.map((page) => ({
      label: page.name,
      value: page.id.toString(),
    }));
  }, [wordpressPagesQuery.data]);

  // const PaymentSettingsAdminCommission = registry.get('PaymentSettingsAdminCommission');
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
            {eCommerceEngine === 'native' && (
              <SelectField
                control={form.control}
                name="checkout_page"
                options={pageOptions}
                label={__('Checkout Page', 'growfund')}
                placeholder={__('Select a checkout page', 'growfund')}
              />
            )}
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

        {/* Admin commission */}
        {/* <ElementWrapper
          feature="settings.payment.admin_commission"
          fallback={<PaymentSettingsAdminCommissionFallback />}
        >
          {PaymentSettingsAdminCommission && <PaymentSettingsAdminCommission />}
        </ElementWrapper> */}

        {/* Wallet settings */}
        {/* <Card>
          <CardHeader>
            <div className="growfund-flex growfund-items-center growfund-justify-between">
              <CardTitle>{__('Wallet', 'growfund')}</CardTitle>
              <SwitchField control={form.control} name="enable_wallet" />
            </div>
            <CardDescription>{__('Manage user wallet settings.', 'growfund')}</CardDescription>
          </CardHeader>
          {enableWallet && (
            <CardContent className="growfund-space-y-4">
              <CheckboxField
                control={form.control}
                name="put_limit_on_revenue_withdrawal"
                label={__('Put limit on revenue withdrawal request', 'growfund')}
              />

              {limitOnRevenueWithdrawal && (
                <Box>
                  <BoxContent className="growfund-space-y-4">
                    <SelectField
                      control={form.control}
                      name="revenue_withdrawal_type"
                      options={[
                        { label: __('Anytime', 'growfund'), value: 'anytime' },
                        {
                          label: __('After a certain percentage of gaol is reached', 'growfund'),
                          value: 'after-certain-goal-reached',
                        },
                        {
                          label: __('After campaign completion', 'growfund'),
                          value: 'after-completion',
                        },
                      ]}
                      label={__('Withdrawal type', 'growfund')}
                      placeholder={__('Select withdrawal type', 'growfund')}
                      description={__(
                        'Specify when fundraisers can request withdrawal.',
                        'growfund',
                      )}
                    />

                    {revenueWithdrawalType === 'after-certain-goal-reached' && (
                      <TextField
                        control={form.control}
                        name="goal_percentage"
                        type="number"
                        label={__('% of goal reached', 'growfund')}
                        placeholder={__('e.g. 80%', 'growfund')}
                      />
                    )}

                    <TextField
                      control={form.control}
                      name="minimum_balance_to_request_withdrawal"
                      label={__('Minimum balance to request withdrawal', 'growfund')}
                      type="number"
                      placeholder={`e.g. ${toCurrency(20)}`}
                    />
                  </BoxContent>
                </Box>
              )}
              <SwitchField
                control={form.control}
                name="enable_wallet_deposit_for_contributors"
                label={
                  isDonationMode
                    ? __('Enable wallet deposit for donors', 'growfund')
                    : __('Enable wallet deposit for backers', 'growfund')
                }
                description={__(
                  'Allow users to deposit money to contribute later from their wallet balance.',
                  'growfund',
                )}
              />
            </CardContent>
          )}
        </Card> */}

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
