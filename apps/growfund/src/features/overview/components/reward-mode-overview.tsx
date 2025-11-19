import AnalyticsRevenueChart from '@/features/analytics/components/contents/analytics-revenue-chart';
import CampaignInformationMetrics from '@/features/analytics/components/contents/campaign-information-metrics';
import RecentPledges from '@/features/analytics/components/contents/recent-pledges';
import TopBackers from '@/features/analytics/components/contents/top-backers';
import TopCampaigns from '@/features/analytics/components/shared/top-campaigns';

const RewardModeOverview = () => {
  return (
    <div className="growfund-mt-4 growfund-space-y-7">
      <CampaignInformationMetrics />
      <AnalyticsRevenueChart />
      <div className="growfund-grid growfund-grid-cols-[23.75rem_auto] growfund-gap-7">
        <div className="growfund-space-y-7">
          <RecentPledges />
          <TopBackers />
        </div>
        <TopCampaigns />
      </div>
    </div>
  );
};

export default RewardModeOverview;
