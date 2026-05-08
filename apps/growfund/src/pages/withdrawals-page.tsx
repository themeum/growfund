import ElementWrapper from '@/components/element-wrapper';
import WithdrawalRequestFallback from '@/components/pro-fallbacks/withdrawal-request-fallback';
import { registry } from '@/lib/registry';

const WithdrawalsPage = () => {
  const WithdrawalListingPage = registry.get('WithdrawalRequestPage');
  return (
    <ElementWrapper fallback={<WithdrawalRequestFallback />}>
      {WithdrawalListingPage && <WithdrawalListingPage />}
    </ElementWrapper>
  );
};

export default WithdrawalsPage;
