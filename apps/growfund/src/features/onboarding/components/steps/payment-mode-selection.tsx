import { __ } from '@wordpress/i18n';
import { ArrowLeft, Check, CheckCircle } from 'lucide-react';
import { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import { NativePaymentIcon, WooCommercePaymentIcon } from '@/app/icons';
import { ComboBoxField } from '@/components/form/combobox-field';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import { growfundConfig } from '@/config/growfund';
import DecisionBox from '@/features/onboarding/components/decision-box';
import { type PaymentMode, useOnboarding } from '@/features/onboarding/contexts/onboarding-context';
import {
    Screen,
    ScreenContent,
    ScreenDescription,
    ScreenFooter,
    ScreenIndicator,
    ScreenTitle,
} from '@/features/onboarding/layouts/screen';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import { countriesAsOptions, getCountryByCode } from '@/utils/countries';
import { currenciesAsOptions } from '@/utils/currencies';

const paymentModes: {
  mode: PaymentMode;
  title: string;
  description: string;
  icon: React.ElementType;
}[] = [
  {
    mode: 'native',
    title: __('Native Payment', 'growfund'),
    description: __(
      'Process payments directly through your platform with lower fees and simplified setup. ',
      'growfund',
    ),
    icon: NativePaymentIcon,
  },
  {
    mode: 'woo-commerce',
    title: __('WooCommerce', 'growfund'),
    description: __('Use WooCommerce to manage payments, invoices and more.', 'growfund'),
    icon: WooCommercePaymentIcon,
  },
];

const PaymentSelection = () => {
  const { setStep, paymentMode, setPaymentMode, setBaseCountry, onComplete, setCurrency, loading } =
    useOnboarding();

  const form = useForm<{ country: string; currency: string }>();

  const country = useWatch({ control: form.control, name: 'country' });
  const currencyValue = useWatch({ control: form.control, name: 'currency' });

  useEffect(() => {
    setBaseCountry(country);
  }, [country, setBaseCountry]);

  useEffect(() => {
    if (isDefined(currencyValue)) {
      setCurrency(currencyValue);
    }
  }, [currencyValue, setCurrency]);

  useEffect(() => {
    if (!isDefined(country)) {
      return;
    }
    const countryObject = getCountryByCode(country);
    if (countryObject) {
      form.setValue('currency', `${countryObject.currency_symbol}:${countryObject.currency}`);
    }
  }, [country, form]);

  return (
    <>
      <DecisionBox className="growfund-p-0">
        <Image
          src="/images/payment-mode.webp"
          className="growfund-size-full growfund-border-none growfund-bg-transparent"
          fit="cover"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenIndicator />
          <ScreenContent className="growfund-flex growfund-flex-col growfund-justify-between growfund-pb-6">
            <div>
              {growfundConfig.is_woocommerce_installed && (
                <>
                  <div className="growfund-space-y-3">
                    <ScreenTitle>
                      {__('How would you like to accept payment?', 'growfund')}
                    </ScreenTitle>
                    <ScreenDescription>
                      {__(`Select a method through which you'll accept payments.`, 'growfund')}
                    </ScreenDescription>
                  </div>
                  <div className={'growfund-grid growfund-grid-cols-2 growfund-gap-4 growfund-mt-4'}>
                    {paymentModes.map((mode) => {
                      const isActive = mode.mode === paymentMode;
                      const Icon = mode.icon;
                      return (
                        <Box
                          key={mode.mode}
                          className={cn(
                            'growfund-shadow-none growfund-border growfund-cursor-pointer hover:growfund-border-border-hover',
                            isActive && 'growfund-border-border-brand growfund-bg-[#EBFFF8]',
                          )}
                          onClick={() => {
                            setPaymentMode(mode.mode);
                          }}
                        >
                          <BoxContent className="growfund-p-4 growfund-flex growfund-flex-col growfund-items-center growfund-text-center growfund-gap-2 growfund-relative">
                            <Icon className="growfund-shrink-0 growfund-size-[3.875rem]" />
                            <div className="growfund-space-y-3">
                              <p className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                                {mode.title}
                              </p>
                              <p className="growfund-typo-tiny growfund-font-[11px] growfund-text-fg-secondary">
                                {mode.description}
                              </p>
                            </div>

                            {isActive && (
                              <div className="growfund-absolute growfund-top-[-10px] growfund-left-[-10px] growfund-size-5 growfund-bg-background-fill-brand growfund-rounded-full growfund-flex growfund-items-center growfund-justify-center">
                                <Check className="growfund-size-3 growfund-text-white" />
                              </div>
                            )}
                          </BoxContent>
                        </Box>
                      );
                    })}
                  </div>
                </>
              )}

              <div className="growfund-mt-6 growfund-space-y-4">
                <Form {...form}>
                  <ComboBoxField
                    control={form.control}
                    name="country"
                    label={__('What country would you like to perform operations?', 'growfund')}
                    options={countriesAsOptions()}
                    placeholder={__('Select your country', 'growfund')}
                  />
                  <ComboBoxField
                    control={form.control}
                    name="currency"
                    label={__('Select currency', 'growfund')}
                    options={currenciesAsOptions()}
                    placeholder={__('Select your currency', 'growfund')}
                  />
                </Form>
              </div>
            </div>

            <ScreenFooter className="growfund-static">
              <Button
                variant="ghost"
                onClick={() => {
                  setStep('mode-selection');
                }}
              >
                <ArrowLeft />
                {__('Back', 'growfund')}
              </Button>
              <Button
                onClick={onComplete}
                disabled={
                  !isDefined(paymentMode) || !isDefined(country) || !isDefined(currencyValue)
                }
                loading={loading}
              >
                <CheckCircle />
                {__('Complete', 'growfund')}
              </Button>
            </ScreenFooter>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default PaymentSelection;
