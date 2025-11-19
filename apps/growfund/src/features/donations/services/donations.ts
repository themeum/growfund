import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Donation, type DonationStatus } from '@/features/donations/schemas/donation';
import { type DonationPayload } from '@/features/donations/schemas/donation-form';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { toPaginatedQueryParams } from '@/utils';

interface DonationsQueryParams extends BaseQueryParams {
  search?: string;
  status?: string;
  start_date?: string;
  campaign_id?: string;
  end_date?: string;
  fund_id?: string;
  user_id?: string;
}

const bulkActions = (payload: {
  ids: string[];
  action: 'delete' | 'trash' | 'reassign_fund' | 'restore';
  is_permanent_delete?: boolean;
  fund_id?: string | null;
}) => {
  return apiClient.patch<{ message: string }>(endpoints.DONATIONS_BULK_ACTIONS, payload);
};

const useDonationBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkActions,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
      void queryClient.invalidateQueries({ queryKey: ['DonationDetails'] });
      void queryClient.invalidateQueries({ queryKey: ['Funds'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const getDonations = (params: DonationsQueryParams) => {
  return apiClient
    .get<PaginatedResponse<Donation>>(endpoints.DONATIONS, {
      params: toPaginatedQueryParams(params),
    })
    .then((response) => response.data);
};
const useDonationsQuery = (params: DonationsQueryParams) => {
  return useQuery({
    queryKey: ['Donations', params],
    queryFn: () => getDonations(params),
    placeholderData: keepPreviousData,
  });
};

const createDonation = (payload: DonationPayload) => {
  return apiClient.post(endpoints.DONATIONS, payload);
};

const useCreateDonationMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createDonation,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
      toast.success(__('Donations created successfully', 'growfund'));
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const updateDonation = ({ id, ...payload }: DonationPayload & { id: string }) => {
  return apiClient.put(endpoints.DONATIONS_WITH_ID(id), payload);
};

const useUpdateDonationMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateDonation,
    onSuccess() {
      toast.success(__('Donation updated successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};
const getDonationDetails = (id: string) => {
  return apiClient.get<Donation>(endpoints.DONATIONS_WITH_ID(id)).then((response) => response.data);
};

const useDonationDetailsQuery = (id: string) => {
  return useQuery({
    queryKey: ['DonationDetails', id],
    queryFn: () => getDonationDetails(id),
  });
};

const emptyDonationsTrash = () => {
  return apiClient.delete(endpoints.EMPTY_DONATIONS_TRASH);
};

const useEmptyDonationsTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyDonationsTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const updateDonationStatus = ({ id, status }: { id: string; status: DonationStatus }) => {
  return apiClient.patch(endpoints.DONATIONS_WITH_ID(id), { action: status });
};

const useUpdateDonationStatusMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateDonationStatus,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
      void queryClient.invalidateQueries({ queryKey: ['DonationDetails'] });
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Donation status updated successfully', 'growfund'));
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const deleteDonation = ({ id, is_permanent }: { id: string; is_permanent: boolean }) => {
  return apiClient.delete(endpoints.DONATIONS_WITH_ID(id), {
    params: {
      is_permanent,
    },
  });
};

const useDeleteDonationMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteDonation,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Donations'] });
      void queryClient.invalidateQueries({ queryKey: ['DonationDetails'] });
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Donation deleted successfully.', 'growfund'));
    },
  });
};

export {
  useCreateDonationMutation,
  useDeleteDonationMutation,
  useDonationBulkActionsMutation,
  useDonationDetailsQuery,
  useDonationsQuery,
  useEmptyDonationsTrashMutation,
  useUpdateDonationMutation,
  useUpdateDonationStatusMutation,
};
