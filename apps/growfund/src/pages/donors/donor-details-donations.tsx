import FeatureGuard from '@/components/feature-guard';
import DonationListFallback from '@/components/pro-fallbacks/donor/donation-list-fallback';
import { registry } from '@/lib/registry';

const DonorDetailsDonationsPage = () => {
  const DonorDonationList = registry.get('DonorDonationList');
  return (
    <div className="growfund-mt-4">
      <FeatureGuard feature="donor.overview" fallback={<DonationListFallback />}>
        {DonorDonationList && <DonorDonationList />}
      </FeatureGuard>
    </div>
  );
};

export default DonorDetailsDonationsPage;
