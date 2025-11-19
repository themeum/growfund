import FeatureGuard from '@/components/feature-guard';
import FundraisersFallback from '@/components/pro-fallbacks/fundraisers-fallback';
import { registry } from '@/lib/registry';

const FundraiserDetailsLayout = () => {
  const FundraiserDetailsLayout = registry.get('FundraiserDetailsLayout');
  return (
    <FeatureGuard feature="fundraisers" fallback={<FundraisersFallback />}>
      {FundraiserDetailsLayout && <FundraiserDetailsLayout />}
    </FeatureGuard>
  );
};

export default FundraiserDetailsLayout;
