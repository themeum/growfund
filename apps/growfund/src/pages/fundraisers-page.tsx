import ElementWrapper from '@/components/element-wrapper';
import FundraisersFallback from '@/components/pro-fallbacks/fundraisers-fallback';
import { registry } from '@/lib/registry';
const FundraisersPage = () => {
  const FundraiserListingPage = registry.get('FundraiserListingPage');
  return (
    <ElementWrapper fallback={<FundraisersFallback />}>
      {FundraiserListingPage && <FundraiserListingPage />}
    </ElementWrapper>
  );
};

export default FundraisersPage;
