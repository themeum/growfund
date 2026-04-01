import { createContext, type PropsWithChildren, use, useEffect, useMemo } from 'react';

import { Box, BoxContent } from '@/components/ui/box';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import { useCampaign } from '@/features/campaigns/contexts/campaign-context';
import { type Reward } from '@/features/campaigns/schemas/reward';
import { type RewardItem } from '@/features/campaigns/schemas/reward-item';
import { useCampaignRewardsQuery } from '@/features/campaigns/services/reward';
import { useGetRewardItemsQuery } from '@/features/campaigns/services/reward-item';
import { matchQueryStatus } from '@/utils/match-query-status';

interface CampaignRewardContextType {
  rewards: Reward[];
  rewardItems: RewardItem[];
}

const CampaignRewardContext = createContext<CampaignRewardContextType | null>(null);

const useCampaignReward = () => {
  const context = use(CampaignRewardContext);

  if (!context) {
    throw new Error('useCampaignReward must be used within a CampaignRewardProvider');
  }

  return context;
};

const CampaignRewardProvider = ({ children }: PropsWithChildren) => {
  const { campaignId } = useCampaignBuilderContext();
  const { updateCampaign } = useCampaign();

  const campaignRewardsQuery = useCampaignRewardsQuery(campaignId);
  const getRewardItemsQuery = useGetRewardItemsQuery({ campaign_id: campaignId ?? null });

  const rewards = useMemo(() => {
    if (!campaignRewardsQuery.data) {
      return [];
    }
    return campaignRewardsQuery.data;
  }, [campaignRewardsQuery.data]);

  const rewardItems = useMemo(() => {
    if (!getRewardItemsQuery.data) {
      return [];
    }

    return getRewardItemsQuery.data;
  }, [getRewardItemsQuery.data]);

  useEffect(() => {
    if (rewards.length > 0) {
      updateCampaign((prev) => {
        return {
          ...prev,
          allow_local_pickup: rewards.some((reward) => reward.allow_local_pickup),
        };
      });
    }
  }, [rewards, updateCampaign]);

  return (
    <CampaignRewardContext value={{ rewards, rewardItems }}>
      {matchQueryStatus(getRewardItemsQuery, {
        Loading: (
          <div className="growfund-flex growfund-flex-col growfund-gap-4 growfund-rounded-md growfund-bg-background-surface-secondary growfund-py-2 growfund-px-4">
            <div className="growfund-flex growfund-items-center growfund-justify-between growfund-h-12">
              <Skeleton animate className="growfund-h-5 growfund-w-[20%]" />
              <Skeleton animate className="growfund-h-8 growfund-w-[20%]" />
            </div>
            <Separator />
            {Array.from({ length: 5 }).map((_, index) => (
              <Box key={index}>
                <BoxContent className="growfund-grid growfund-grid-cols-[1fr_2fr_2fr_1fr] growfund-gap-4">
                  <Skeleton className="growfund-h-5 growfund-w-10" animate />
                  <div className="growfund-space-y-4">
                    <Skeleton animate className="growfund-h-5 growfund-w-full" />
                    <Skeleton animate className="growfund-h-5 growfund-w-[90%]" />
                    <Skeleton animate className="growfund-h-5 growfund-w-[60%]" />
                  </div>
                  <div className="growfund-space-y-4">
                    <Skeleton animate className="growfund-h-5 growfund-w-full" />
                    <Skeleton animate className="growfund-h-5 growfund-w-[90%]" />
                    <Skeleton animate className="growfund-h-5 growfund-w-[60%]" />
                  </div>
                  <Skeleton animate className="growfund-h-32 growfund-w-32 growfund-rounded-md" />
                </BoxContent>
                <Separator />
                <div className="growfund-flex growfund-items-center growfund-justify-between growfund-p-3">
                  <Skeleton animate className="growfund-h-5 growfund-w-[30%]" />
                  <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-w-[30%]">
                    <Skeleton animate className="growfund-h-5 growfund-w-full" />
                    <Skeleton animate className="growfund-h-5 growfund-w-full" />
                  </div>
                </div>
              </Box>
            ))}
          </div>
        ),
        Error: children,
        Empty: children,
        Success: () => {
          return children;
        },
      })}
    </CampaignRewardContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { CampaignRewardProvider, useCampaignReward };
