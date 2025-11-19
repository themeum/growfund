import { createContext, use, useCallback, useEffect, useState } from 'react';

import { useOnboardingMutation } from '@/services/app-config';

export type OnboardingStep = 'welcome' | 'mode-selection' | 'payment-selection';
export type CampaignMode = 'reward' | 'donation';
export type PaymentMode = 'native' | 'woo-commerce';

interface OnboardingContextType {
  step: OnboardingStep;
  setStep: (step: OnboardingStep) => void;
  campaignMode: CampaignMode | null;
  setCampaignMode: (mode: CampaignMode | null) => void;
  paymentMode: PaymentMode | null;
  setPaymentMode: (mode: PaymentMode | null) => void;
  baseCountry: string | null;
  setBaseCountry: (country: string | null) => void;
  onComplete: () => void;
  loading: boolean;
  currency: string | null;
  setCurrency: (currency: string) => void;
}

const OnboardingContext = createContext<OnboardingContextType | null>(null);

const useOnboarding = () => {
  const context = use(OnboardingContext);
  if (!context) {
    throw new Error('useOnboarding must be used within an OnboardingProvider');
  }
  return context;
};

const OnboardingProvider = ({
  children,
  isMigrating = false,
}: {
  children: React.ReactNode;
  isMigrating?: boolean;
}) => {
  const [step, setStep] = useState<OnboardingStep>('welcome');
  const [campaignMode, setCampaignMode] = useState<CampaignMode | null>(null);
  const [paymentMode, setPaymentMode] = useState<PaymentMode | null>('native');
  const [baseCountry, setBaseCountry] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [currency, setCurrency] = useState<string | null>(null);

  const onboardingMutation = useOnboardingMutation(isMigrating);

  const onComplete = useCallback(() => {
    if (!campaignMode || !paymentMode || !baseCountry || !currency) {
      return;
    }

    onboardingMutation.mutate(
      {
        campaign_mode: campaignMode,
        payment_mode: paymentMode,
        base_country: baseCountry,
        currency: currency,
      },
      {
        onSuccess() {
          if (!isMigrating) {
            window.location.reload();
            window.location.href = '/wp-admin/admin.php?page=growfund';
          }
        },
      },
    );
  }, [campaignMode, paymentMode, baseCountry, currency, onboardingMutation, isMigrating]);

  useEffect(() => {
    setLoading(onboardingMutation.isPending);
  }, [onboardingMutation.isPending]);

  return (
    <OnboardingContext
      value={{
        step,
        setStep,
        campaignMode,
        setCampaignMode,
        paymentMode,
        setPaymentMode,
        baseCountry,
        setBaseCountry,
        onComplete,
        loading,
        currency,
        setCurrency,
      }}
    >
      {children}
    </OnboardingContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { OnboardingProvider, useOnboarding };
