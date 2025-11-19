import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type RewardItem, type RewardItemPayload } from '@/features/campaigns/schemas/reward-item';
import { apiClient } from '@/lib/api';
import { assertIsDefined } from '@/utils';

const getRewardItems = (params: { campaign_id: string }) => {
  return apiClient
    .get<RewardItem[]>(endpoints.CAMPAIGN_REWARD_ITEMS(params.campaign_id))
    .then((response) => response.data);
};

const useGetRewardItemsQuery = (params: { campaign_id: string | null }) => {
  return useQuery({
    queryKey: ['RewardItems', params],
    queryFn: () => {
      assertIsDefined(params.campaign_id, __('Campaign ID is required', 'growfund'));
      return getRewardItems({ campaign_id: params.campaign_id });
    },
    enabled: !!params.campaign_id,
  });
};

const createRewardItem = ({
  campaign_id,
  ...payload
}: RewardItemPayload & { campaign_id: string }) => {
  return apiClient
    .post<{ id: string }>(endpoints.CAMPAIGN_REWARD_ITEMS(campaign_id), payload)
    .then((response) => response.data);
};

const useCreateRewardItemMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createRewardItem,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['RewardItems'] });
      toast.success(__('Reward item created successfully', 'growfund'));
    },
  });
};

const updateRewardItem = ({
  campaign_id,
  id,
  ...payload
}: RewardItemPayload & { campaign_id: string; id: string }) => {
  return apiClient.put(endpoints.CAMPAIGN_REWARD_ITEMS_WITH_ID(campaign_id, id), payload);
};

const useUpdateRewardItemMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateRewardItem,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['RewardItems'] });
      toast.success(__('Reward item updated successfully', 'growfund'));
    },
  });
};

const deleteRewardItem = ({ campaign_id, id }: { campaign_id: string; id: string }) => {
  return apiClient.delete(endpoints.CAMPAIGN_REWARD_ITEMS_WITH_ID(campaign_id, id));
};

const useDeleteRewardItemMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteRewardItem,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['RewardItems'] });
      toast.success(__('Reward item deleted successfully', 'growfund'));
    },
  });
};

export {
  useCreateRewardItemMutation,
  useDeleteRewardItemMutation,
  useGetRewardItemsQuery,
  useUpdateRewardItemMutation,
};
