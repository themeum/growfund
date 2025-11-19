import FeatureGuard from '@/components/feature-guard';
import FundraisersFallback from '@/components/pro-fallbacks/fundraisers-fallback';
import { registry } from '@/lib/registry';
const FundraisersPage = () => {
  const FundraiserListingPage = registry.get('FundraiserListingPage');
  return (
    <FeatureGuard feature="fundraisers" fallback={<FundraisersFallback />}>
      {FundraiserListingPage && <FundraiserListingPage />}
    </FeatureGuard>
  );
};

export default FundraisersPage;
