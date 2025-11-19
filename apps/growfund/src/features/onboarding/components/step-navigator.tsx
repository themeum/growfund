import ModeSelection from '@/features/onboarding/components/steps/campaign-mode-selection';
import PaymentSelection from '@/features/onboarding/components/steps/payment-mode-selection';
import Welcome from '@/features/onboarding/components/steps/welcome';
import { useOnboarding } from '@/features/onboarding/contexts/onboarding-context';

const StepNavigator = () => {
  const { step } = useOnboarding();

  if (step === 'welcome') {
    return <Welcome />;
  }

  if (step === 'mode-selection') {
    return <ModeSelection />;
  }

  return <PaymentSelection />;
};

export default StepNavigator;
