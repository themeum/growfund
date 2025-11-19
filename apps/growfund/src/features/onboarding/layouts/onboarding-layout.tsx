import { BrandWhiteIcon, BridgeIcon } from '@/app/icons';
import StepNavigator from '@/features/onboarding/components/step-navigator';
import { OnboardingProvider } from '@/features/onboarding/contexts/onboarding-context';

const OnboardingLayout = () => {
  return (
    <OnboardingProvider>
      <div className="growfund-flex growfund-flex-col growfund-gap-5 growfund-items-start">
        <BrandWhiteIcon className="growfund-h-6 growfund-ms-10" />
        <div className="growfund-relative growfund-h-full growfund-flex growfund-items-center growfund-justify-center growfund-w-[min(100vw,58.75rem)] growfund-mx-10">
          <div className="growfund-grid growfund-grid-cols-[42.5532%_1fr] growfund-gap-[2.625rem] growfund-min-h-[min(28.25rem,90svh)] growfund-w-full">
            <StepNavigator />
          </div>
          <div className="growfund-absolute growfund-top-[50%] growfund-left-[42.5532%] growfund-translate-y-[-50%]">
            <BridgeIcon />
          </div>
        </div>
      </div>
    </OnboardingProvider>
  );
};

export default OnboardingLayout;
