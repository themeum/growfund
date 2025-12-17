import ElementWrapper from '@/components/element-wrapper';
import CampaignSuggestedOptionsFallback from '@/components/pro-fallbacks/campaign/campaign-suggested-options-fallbacks';
import { AmountDescription } from '@/features/campaigns/components/goal-step/amount-description';
import { registry } from '@/lib/registry';
import { cn } from '@/lib/utils';

const DonationPresets = () => {
  const CampaignSuggestedOptions = registry.get('CampaignSuggestedOptions');

  return (
    <div
      className={cn(
        'growfund-rounded-lg growfund-bg-background-surface growfund-overflow-hidden growfund-flex growfund-flex-col growfund-gap-4',
      )}
    >
      <ElementWrapper fallback={<CampaignSuggestedOptionsFallback />}>
        {CampaignSuggestedOptions && <CampaignSuggestedOptions />}
      </ElementWrapper>

      <AmountDescription />
    </div>
  );
};
export default DonationPresets;
