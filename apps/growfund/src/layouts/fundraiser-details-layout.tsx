import ElementWrapper from '@/components/element-wrapper';
import FundraisersFallback from '@/components/pro-fallbacks/fundraisers-fallback';
import { registry } from '@/lib/registry';

const FundraiserDetailsLayout = () => {
  const FundraiserDetailsLayout = registry.get('FundraiserDetailsLayout');
  return (
    <ElementWrapper fallback={<FundraisersFallback />}>
      {FundraiserDetailsLayout && <FundraiserDetailsLayout />}
    </ElementWrapper>
  );
};

export default FundraiserDetailsLayout;
