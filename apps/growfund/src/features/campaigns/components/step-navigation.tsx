import { __ } from '@wordpress/i18n';
import { ArrowLeft, ArrowRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';

const StepNavigation = () => {
  const { navigateNextStep, navigatePreviousStep, isFirstStep, isLastStep } =
    useCampaignBuilderContext();

  if (!isLastStep && !isFirstStep) {
    return (
      <div className="growfund-flex growfund-items-center growfund-gap-4">
        <Button variant="outline" onClick={navigatePreviousStep}>
          <ArrowLeft />
          {__('Previous', 'growfund')}
        </Button>
        <Button variant="outline" onClick={navigateNextStep}>
          {__('Next', 'growfund')}
          <ArrowRight />
        </Button>
      </div>
    );
  }

  if (isFirstStep) {
    return (
      <Button variant="outline" onClick={navigateNextStep}>
        {__('Next', 'growfund')}
        <ArrowRight />
      </Button>
    );
  }

  if (isLastStep) {
    return (
      <Button variant="outline" onClick={navigatePreviousStep}>
        <ArrowLeft />
        {__('Previous', 'growfund')}
      </Button>
    );
  }
};

export default StepNavigation;
