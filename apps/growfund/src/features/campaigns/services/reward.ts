import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Reward, type RewardPayload } from '@/features/campaigns/schemas/reward';
import { apiClient } from '@/lib/api';
import { assertIsDefined } from '@/utils';

const getCampaignRewards = (campaign_id: string) => {
  return apiClient
    .get<Reward[]>(endpoints.CAMPAIGN_REWARDS(campaign_id))
    .then((response) => response.data);
};

const useCampaignRewardsQuery = (campaign_id: string | undefined) => {
  return useQuery({
    queryKey: ['CampaignRewards', campaign_id],
    queryFn: () => {
      assertIsDefined(campaign_id, __('Campaign ID is required', 'growfund'));
      return getCampaignRewards(campaign_id);
    },
    enabled: !!campaign_id,
  });
};

const createReward = ({ campaign_id, ...payload }: RewardPayload & { campaign_id: string }) => {
  return apiClient.post(endpoints.CAMPAIGN_REWARDS(campaign_id), payload);
};

const useCreateRewardMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createReward,
    onSuccess: () => {
      toast.success(__('Reward created successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['CampaignRewards'] });
    },
    onError: (error) => {
      toast.error(error.message);
    },
  });
};

const updateReward = ({
  campaign_id,
  reward_id,
  ...payload
}: RewardPayload & { campaign_id: string; reward_id: string }) => {
  return apiClient.put(endpoints.CAMPAIGN_REWARD_WITH_ID(campaign_id, reward_id), payload);
};

const useUpdateRewardMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateReward,
    onSuccess: () => {
      toast.success(__('Reward updated successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['CampaignRewards'] });
    },
    onError: (error) => {
      toast.error(error.message);
    },
  });
};

const deleteReward = ({ campaign_id, reward_id }: { campaign_id: string; reward_id: string }) => {
  return apiClient.delete(endpoints.CAMPAIGN_REWARD_WITH_ID(campaign_id, reward_id));
};

const useDeleteCampaignRewardMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteReward,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['CampaignRewards'] });
      toast.success(__('Reward is deleted from the campaign.', 'growfund'));
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

export {
  useCampaignRewardsQuery,
  useCreateRewardMutation,
  useDeleteCampaignRewardMutation,
  useUpdateRewardMutation,
};
