import ElementWrapper from '@/components/element-wrapper';
import ActivityLogFallback from '@/components/pro-fallbacks/activity-log-fallback';
import DonorMetricsFallback from '@/components/pro-fallbacks/donor/metrics-fallback';
import UserPreviewCard from '@/components/users/user-preview-card';
import { useDonorContext } from '@/features/donors/contexts/donor';
import { registry } from '@/lib/registry';

const DonorDetailsOverviewPage = () => {
  const { donor } = useDonorContext();

  const DonorOverviewMetrics = registry.get('DonorOverviewMetrics');
  const DonorActivityLogCard = registry.get('DonorActivityLogCard');
  return (
    <div className="growfund-space-y-4 growfund-mt-4">
      <ElementWrapper fallback={<DonorMetricsFallback />}>
        {DonorOverviewMetrics && <DonorOverviewMetrics />}
      </ElementWrapper>
      <div className="growfund-grid growfund-grid-cols-[320px_auto] growfund-gap-4">
        <UserPreviewCard user={donor.profile} hideHeader />
        <ElementWrapper fallback={<ActivityLogFallback />}>
          {DonorActivityLogCard && <DonorActivityLogCard />}
        </ElementWrapper>
      </div>
    </div>
  );
};

export default DonorDetailsOverviewPage;
