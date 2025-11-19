import { Container } from '@/components/layouts/container';
import { useAppConfig } from '@/contexts/app-config';
import StepNavigation from '@/features/campaigns/components/step-navigation';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { registry } from '@/lib/registry';

const AdditionalPage = () => {
  const { appConfig } = useAppConfig();
  const CampaignFundSelection = registry.get('CampaignFundSelection');
  const CampaignTributeForm = registry.get('CampaignTributeForm');

  return (
    <Container size="xs" className="growfund-space-y-3">
      {appConfig[AppConfigKeys.Campaign]?.allow_tribute && CampaignTributeForm && (
        <CampaignTributeForm />
      )}

      {appConfig[AppConfigKeys.Campaign]?.allow_fund && CampaignFundSelection && (
        <CampaignFundSelection />
      )}

      <div className="growfund-flex growfund-mt-4 growfund-justify-end">
        <StepNavigation />
      </div>
    </Container>
  );
};

export default AdditionalPage;
