import { registry } from '@/lib/registry';

const FundraiserDetailsOverview = () => {
  const FundraiserOverview = registry.get('FundraiserOverview');
  return FundraiserOverview && <FundraiserOverview />;
};

export default FundraiserDetailsOverview;
