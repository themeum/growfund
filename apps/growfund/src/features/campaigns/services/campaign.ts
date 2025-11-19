import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import {
  type Campaign,
  type CampaignPayload,
  type CampaignStatus,
} from '@/features/campaigns/schemas/campaign';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { toPaginatedQueryParams } from '@/utils';

interface QueryParams extends BaseQueryParams {
  search?: string;
  start_date?: string;
  end_date?: string;
  status?: string;
  fundraiser_id?: string;
}

const getCampaigns = (params: QueryParams) => {
  return apiClient
    .get<PaginatedResponse<Campaign>>(endpoints.CAMPAIGNS, {
      params: toPaginatedQueryParams(params),
    })
    .then((response) => response.data);
};

const useCampaignsQuery = (params: QueryParams) => {
  return useQuery({
    queryKey: ['Campaigns', params],
    queryFn: () => getCampaigns(params),
    placeholderData: keepPreviousData,
  });
};

const getCampaignDetails = (id: string) => {
  return apiClient.get<Campaign>(endpoints.CAMPAIGNS_WITH_ID(id)).then((response) => response.data);
};

const useCampaignDetailsQuery = (id: string) => {
  return useQuery({
    queryKey: ['CampaignDetails', id],
    queryFn: () => getCampaignDetails(id),
  });
};

const createCampaign = () => {
  return apiClient.post<{ id: string }>(endpoints.CAMPAIGNS).then((response) => response.data);
};

const useCreateCampaignMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createCampaign,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const updateCampaign = ({ id, ...payload }: CampaignPayload & { id: string }) => {
  return apiClient.put(endpoints.CAMPAIGNS_WITH_ID(id), payload);
};

const useUpdateCampaignMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateCampaign,
    onSuccess() {
      toast.success(__('Campaign updated successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['CampaignDetails'] });
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
      void queryClient.invalidateQueries({ queryKey: ['CampaignRewards'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const bulkActions = (payload: {
  ids: string[];
  action: 'delete' | 'trash' | 'restore' | 'featured' | 'non-featured';
}) => {
  return apiClient.patch<{ message: string }>(endpoints.CAMPAIGNS_BULK_ACTIONS, payload);
};

const useCampaignBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkActions,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
      void queryClient.invalidateQueries({ queryKey: ['CampaignDetails'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const approveCampaign = (id: string) => {
  return apiClient.patch(endpoints.CAMPAIGNS_WITH_ID(id), {
    status: 'published',
  });
};

const declineCampaign = ({ id, decline_reason }: { id: string; decline_reason: string }) => {
  return apiClient.patch(endpoints.CAMPAIGNS_WITH_ID(id), {
    status: 'declined',
    decline_reason,
  });
};

const useApproveCampaignMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: approveCampaign,
    onSuccess() {
      toast.success(__('Campaign approved successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const useDeclineCampaignMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: declineCampaign,
    onSuccess() {
      toast.success(__('Campaign declined successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const deleteCampaign = (id: string) => {
  return apiClient.delete(endpoints.CAMPAIGNS_WITH_ID(id));
};

const useDeleteCampaignMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteCampaign,
    onSuccess() {
      toast.success(__('Campaign deleted successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const emptyCampaignsTrash = () => {
  return apiClient.delete(endpoints.EMPTY_CAMPAIGNS_TRASH);
};

const useEmptyCampaignsTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyCampaignsTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

interface UpdateStatusBasePayload {
  id: string;
}

interface DeclineCampaignPayload extends UpdateStatusBasePayload {
  status: 'declined';
  decline_reason: string;
}
interface OtherStatusPayload extends UpdateStatusBasePayload {
  status: Exclude<CampaignStatus, 'declined'>;
}
type UpdateCampaignStatusPayload = DeclineCampaignPayload | OtherStatusPayload;

const updateCampaignStatus = ({ id, ...payload }: UpdateCampaignStatusPayload) => {
  return apiClient.patch(endpoints.CAMPAIGNS_WITH_ID(id), payload);
};

const useUpdateCampaignStatusMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateCampaignStatus,
    onSuccess() {
      toast.success(__('Campaign status updated successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Campaigns'] });
      void queryClient.invalidateQueries({ queryKey: ['CampaignDetails'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const updateCampaignSecondaryStatus = ({
  id,
  ...params
}: {
  id: string;
  status: 'end' | 'hide' | 'visible' | 'pause' | 'resume';
}) => {
  return apiClient.patch(endpoints.CAMPAIGN_UPDATE_SECONDARY_STATUS(id), params);
};

const useUpdateCampaignSecondaryStatusMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateCampaignSecondaryStatus,
    onSuccess(data) {
      toast.success((data as unknown as { message: string }).message);
      void queryClient.invalidateQueries({ queryKey: ['CampaignDetails'] });
    },
  });
};

const chargeBackers = ({ campaign_id }: { campaign_id: string }) => {
  return apiClient.post(endpoints.CHARGE_BACKERS(campaign_id));
};

const useChargeBackersMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: chargeBackers,
    onSuccess() {
      toast.success(__('Backers are scheduled for charging.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['CampaignDetails'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

export {
  useApproveCampaignMutation,
  useCampaignBulkActionsMutation,
  useCampaignDetailsQuery,
  useCampaignsQuery,
  useChargeBackersMutation,
  useCreateCampaignMutation,
  useDeclineCampaignMutation,
  useDeleteCampaignMutation,
  useEmptyCampaignsTrashMutation,
  useUpdateCampaignMutation,
  useUpdateCampaignSecondaryStatusMutation,
  useUpdateCampaignStatusMutation,
};
