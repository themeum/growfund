import { useAppConfig } from '@/contexts/app-config';
import CampaignInformationMetrics from '@/features/analytics/components/contents/campaign-information-metrics';
import DonationInformationMetrics from '@/features/analytics/components/contents/donation-information-metrics';

const InformationMetrics = ({
  hasNoColor = false,
  invisibleGrowth = false,
  campaignId,
}: {
  hasNoColor?: boolean;
  invisibleGrowth?: boolean;
  campaignId?: string;
}) => {
  const { isDonationMode } = useAppConfig();

  if (isDonationMode) {
    return (
      <DonationInformationMetrics
        hasNoColor={hasNoColor}
        invisibleGrowth={invisibleGrowth}
        campaignId={campaignId}
      />
    );
  }

  return (
    <CampaignInformationMetrics
      hasNoColor={hasNoColor}
      invisibleGrowth={invisibleGrowth}
      campaignId={campaignId}
    />
  );
};

export default InformationMetrics;
