import { Button } from '@/components/ui/button';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import { cn } from '@/lib/utils';

const CampaignNavigation = () => {
  const { steps, activeStep, navigateToStep, errorOn } = useCampaignBuilderContext();

  return (
    <div className="growfund-h-16 growfund-flex growfund-flex-1 growfund-gap-4 growfund-justify-center growfund-items-center">
      {steps.map((step) => {
        return (
          <Button
            key={step.value}
            variant="ghost"
            className={cn(
              'hover:growfund-bg-background-surface-subdued',
              step.value === activeStep && 'growfund-bg-background-white growfund-text-fg-brand',
            )}
            onClick={() => {
              navigateToStep(step.value);
            }}
          >
            <div className="growfund-relative">
              {errorOn?.includes(step.value) && (
                <span className="growfund-absolute growfund-w-2 growfund-h-2 growfund-bg-background-fill-critical growfund-rounded-full growfund-top-[-2px] growfund-left-[-2px] growfund-animate-bounce" />
              )}
              {step.icon}
            </div>
            {step.label}
          </Button>
        );
      })}
    </div>
  );
};

export default CampaignNavigation;
