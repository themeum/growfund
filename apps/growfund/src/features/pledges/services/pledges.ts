import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Pledge, type PledgeStatus } from '@/features/pledges/schemas/pledge';
import { type PledgePayload } from '@/features/pledges/schemas/pledge-form';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { toPaginatedQueryParams } from '@/utils';

interface PledgesQueryParams extends BaseQueryParams {
  search?: string;
  campaign_id?: string;
  status?: PledgeStatus;
  start_date?: string;
  end_date?: string;
  user_id?: string;
}

const getPledges = (params: PledgesQueryParams) => {
  return apiClient
    .get<PaginatedResponse<Pledge>>(endpoints.PLEDGES, {
      params: toPaginatedQueryParams(params),
    })
    .then((response) => response.data);
};

const usePledgesQuery = (params: PledgesQueryParams) => {
  return useQuery({
    queryKey: ['Pledges', params],
    queryFn: () => getPledges(params),
    placeholderData: keepPreviousData,
  });
};

const createNewPledge = (payload: PledgePayload) => {
  return apiClient.post(endpoints.PLEDGES, payload);
};

const useCreateNewPledgeMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createNewPledge,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
      toast.success(__('Pledges created successfully', 'growfund'));
    },
  });
};

const updatePledge = ({ id, ...payload }: PledgePayload & { id: string }) => {
  return apiClient.put(endpoints.PLEDGES_WITH_ID(id), payload);
};

const useUpdatePledgeMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updatePledge,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
      toast.success(__('Pledge updated successfully', 'growfund'));
    },
  });
};

const getPledgeDetails = (id: string) => {
  return apiClient.get<Pledge>(endpoints.PLEDGES_WITH_ID(id)).then((response) => response.data);
};

const usePledgeDetailsQuery = (id: string) => {
  return useQuery({
    queryKey: ['PledgeDetails', id],
    queryFn: () => getPledgeDetails(id),
  });
};

const deletePledge = ({ id, is_permanent }: { id: string; is_permanent: boolean }) => {
  return apiClient.delete(endpoints.PLEDGES_WITH_ID(id), {
    params: {
      is_permanent,
    },
  });
};

const useDeletePledgeMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deletePledge,
    onSuccess(_, id) {
      toast.success(__('Pledge deleted successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
      void queryClient.invalidateQueries({ queryKey: ['Pledges', id] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const emptyPledgesTrash = () => {
  return apiClient.post(endpoints.EMPTY_PLEDGES_TRASH);
};

const useEmptyPledgesTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyPledgesTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const bulkActions = (payload: { ids: string[]; action: 'delete' | 'trash' | 'restore' }) => {
  return apiClient.patch<{ message: string }>(endpoints.PLEDGES_BULK_ACTIONS, payload);
};

const usePledgesBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkActions,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const updatePledgeStatus = ({ id, status }: { id: string; status: PledgeStatus }) => {
  return apiClient.patch(endpoints.PLEDGES_WITH_ID(id), { action: status });
};

const useUpdatePledgeStatusMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updatePledgeStatus,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Pledges'] });
      void queryClient.invalidateQueries({ queryKey: ['PledgeDetails'] });
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Pledge status updated successfully', 'growfund'));
    },
  });
};

const chargeBacker = ({ pledgeId }: { pledgeId: string }) => {
  return apiClient.post(endpoints.CHARGE_BACKER(pledgeId));
};

const useChargePledgedBackerMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: chargeBacker,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['PledgeDetails'] });
      toast.success(__('Backer is scheduled for charging.', 'growfund'));
    },
  });
};

const retryFailedPayment = ({ pledgeId }: { pledgeId: string }) => {
  return apiClient.post(endpoints.RETRY_FAILED_PAYMENT(pledgeId));
};

const useRetryFailedPaymentMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: retryFailedPayment,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['PledgeDetails'] });
      toast.success(__('Payment is scheduled for retrying.', 'growfund'));
    },
  });
};

export {
  useChargePledgedBackerMutation,
  useCreateNewPledgeMutation,
  useDeletePledgeMutation,
  useEmptyPledgesTrashMutation,
  usePledgeDetailsQuery,
  usePledgesBulkActionsMutation,
  usePledgesQuery,
  useRetryFailedPaymentMutation,
  useUpdatePledgeMutation,
  useUpdatePledgeStatusMutation,
};
