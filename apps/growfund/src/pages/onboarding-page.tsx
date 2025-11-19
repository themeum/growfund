import { useEffect } from 'react';

import OnboardingLayout from '@/features/onboarding/layouts/onboarding-layout';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';

const OnboardingPage = () => {
  const { hideWordpressLayout } = useManageWordpressLayout();
  useEffect(() => {
    hideWordpressLayout();
  }, [hideWordpressLayout]);
  return (
    <div className="growfund-w-[100vw] growfund-h-[100svh] growfund-flex growfund-items-center growfund-justify-center growfund-bg-[#406B52]">
      <OnboardingLayout />
    </div>
  );
};

export default OnboardingPage;
