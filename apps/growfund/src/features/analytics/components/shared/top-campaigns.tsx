import { __ } from '@wordpress/i18n';
import { FileText } from 'lucide-react';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useNavigate } from 'react-router';

import { EmptySearchIcon2 } from '@/app/icons';
import CampaignCardMini from '@/components/campaigns/campaign-card-mini';
import { EmptyState, EmptyStateDescription } from '@/components/empty-state';
import { LoadingSkeletonCard } from '@/components/layouts/loading-skeleton';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { InfoTooltip } from '@/components/ui/tooltip';
import { RouteConfig } from '@/config/route-config';
import { type AnalyticsFilter, AnalyticType } from '@/features/analytics/schemas/analytics';
import { useAnalyticsQuery } from '@/features/analytics/services/analytics';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { useDebounce } from '@/hooks/use-debounce';
import { toQueryParamSafe } from '@/lib/date';

const TopCampaigns = () => {
  const navigate = useNavigate();
  const form = useFormContext<AnalyticsFilter>();

  const dateRange = useDebounce(useWatch({ control: form.control, name: 'date_range' }));

  const topCampaignsQuery = useAnalyticsQuery<Campaign[]>(AnalyticType.TopCampaigns, {
    start_date: dateRange?.from ? toQueryParamSafe(dateRange.from) : undefined,
    end_date: dateRange?.to ? toQueryParamSafe(dateRange.to) : undefined,
  });

  const campaigns = useMemo(() => {
    if (!topCampaignsQuery.data) return [];

    return topCampaignsQuery.data;
  }, [topCampaignsQuery.data]);

  if (campaigns.length === 0) {
    return (
      <Box className="growfund-rounded-3xl">
        <BoxContent className="growfund-px-6 growfund-py-4 growfund-h-full growfund-overflow-hidden">
          <BoxTitle>{__('Top Campaigns', 'growfund')}</BoxTitle>
          <EmptyState className="growfund-h-full growfund-shadow-none growfund-pt-0">
            <EmptySearchIcon2 />
            <EmptyStateDescription>{__('No data found.', 'growfund')}</EmptyStateDescription>
          </EmptyState>
        </BoxContent>
      </Box>
    );
  }

  return (
    <Box className="growfund-rounded-3xl">
      <BoxContent className="growfund-px-6 growfund-py-4">
        <BoxTitle className="growfund-justify-between [&>span>[data-type=tooltip]]:growfund-opacity-0 group-hover/box:[&>span>[data-type=tooltip]]:growfund-opacity-100">
          <span>
            {__('Top Campaigns', 'growfund')}
            <InfoTooltip>
              {__(
                'Top Campaigns are the campaigns that have received the most engagement from the users.',
                'growfund',
              )}
            </InfoTooltip>
          </span>
          <Button
            variant="ghost"
            size="sm"
            className="growfund-opacity-0 group-hover/box:growfund-opacity-100"
            onClick={() => {
              void navigate(RouteConfig.Campaigns.buildLink());
            }}
          >
            <FileText className="growfund-size-4" />
            {__('See All Campaigns', 'growfund')}
          </Button>
        </BoxTitle>
        <div className="growfund-space-y-1 growfund-mt-4">
          <LoadingSkeletonCard
            showAvatarSkeleton
            loading={topCampaignsQuery.isFetching || topCampaignsQuery.isLoading}
          >
            {campaigns.map((campaign) => {
              return <CampaignCardMini key={campaign.id} campaign={campaign} />;
            })}
          </LoadingSkeletonCard>
        </div>
      </BoxContent>
    </Box>
  );
};

export default TopCampaigns;
