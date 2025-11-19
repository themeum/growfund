import { registry } from '@/lib/registry';

const FundraiserDetailsCampaigns = () => {
  const FundraiserCampaigns = registry.get('FundraiserCampaigns');
  return FundraiserCampaigns && <FundraiserCampaigns />;
};

export default FundraiserDetailsCampaigns;
