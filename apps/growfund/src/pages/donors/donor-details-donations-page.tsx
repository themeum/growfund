import ElementWrapper from '@/components/element-wrapper';
import DonationListFallback from '@/components/pro-fallbacks/donor/donation-list-fallback';
import { registry } from '@/lib/registry';

const DonorDetailsDonationsPage = () => {
  const DonorDonationList = registry.get('DonorDonationList');
  return (
    <div className="growfund-mt-4">
      <ElementWrapper fallback={<DonationListFallback />}>
        {DonorDonationList && <DonorDonationList />}
      </ElementWrapper>
    </div>
  );
};

export default DonorDetailsDonationsPage;
