import {
  keepPreviousData,
  useInfiniteQuery,
  useMutation,
  useQuery,
  useQueryClient,
} from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Donor, type DonorOverview, type DonorPayload } from '@/features/donors/schemas/donor';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { type Activity } from '@/types/activity';
import { toPaginatedQueryParams } from '@/utils';

interface DonorsQueryParams extends BaseQueryParams {
  search?: string;
  campaign_id?: string;
  status?: string;
  start_date?: string;
  end_date?: string;
}

const getDonors = (params: DonorsQueryParams) => {
  return apiClient
    .get<PaginatedResponse<Donor>>(endpoints.DONORS, {
      params: toPaginatedQueryParams(params),
    })
    .then((response) => response.data);
};

const useDonorsQuery = (params: DonorsQueryParams) => {
  return useQuery({
    queryKey: ['Donors', params],
    queryFn: () => getDonors(params),
    placeholderData: keepPreviousData,
  });
};

const createDonor = (payload: DonorPayload) => {
  return apiClient.post(endpoints.DONORS, payload);
};

const useCreateDonorMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createDonor,
    onSuccess() {
      toast.success(__('Donor created successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donors'] });
    },
  });
};

const updateDonor = ({ id, ...payload }: DonorPayload & { id: string }) => {
  return apiClient.put(endpoints.DONORS_WITH_ID(id), payload);
};

const useUpdateDonorMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateDonor,
    onSuccess() {
      toast.success(__('Donor updated successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donors'] });
      void queryClient.invalidateQueries({ queryKey: ['CurrentUser'] });
      void queryClient.invalidateQueries({ queryKey: ['DonorOverview'] });
    },
  });
};

const deleteDonor = ({ id }: { id: string }) => {
  return apiClient.delete(endpoints.DONORS_WITH_ID(id));
};

const useDeleteDonorMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteDonor,
    onSuccess() {
      toast.success(__('Donor deleted successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donors'] });
    },
  });
};

const getDonorOverview = (id: string) => {
  return apiClient
    .get<DonorOverview>(endpoints.DONORS_OVERVIEW(id))
    .then((response) => response.data);
};

const useGetDonorOverview = (id: string) => {
  return useQuery({
    queryKey: ['DonorOverview', id],
    queryFn: () => getDonorOverview(id),
    enabled: !!id,
  });
};
const bulkActions = (payload: {
  ids: string[];
  is_permanent_delete?: boolean;
  action: 'restore' | 'delete' | 'trash';
}) => {
  return apiClient.patch<{ message: string }>(endpoints.DONORS_BULK_ACTIONS, payload);
};

const useDonorBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkActions,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Donors'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const emptyDonorsTrash = (payload: { delete_associated_donations: boolean }) => {
  return apiClient.delete(endpoints.EMPTY_DONORS_TRASH, {
    data: payload,
  });
};

const useEmptyDonorsTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyDonorsTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Donors'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const getDonorActivities = async (donorId: string, params: BaseQueryParams) => {
  const response = await apiClient.get<PaginatedResponse<Activity>>(
    endpoints.DONOR_ACTIVITIES(donorId),
    {
      params,
    },
  );

  return response.data;
};

const useDonorActivitiesInfiniteQuery = (donorId: string, enabled = true) => {
  return useInfiniteQuery({
    enabled: !!donorId && enabled,
    queryKey: ['DonorActivities', donorId],
    initialPageParam: 1,
    queryFn: ({ pageParam = 1 }) => getDonorActivities(donorId, { page: pageParam }),
    getNextPageParam: (response) => {
      const { current_page, has_more } = response;

      return has_more ? current_page + 1 : undefined;
    },
  });
};

export {
  useCreateDonorMutation,
  useDeleteDonorMutation,
  useDonorActivitiesInfiniteQuery,
  useDonorBulkActionsMutation,
  useDonorsQuery,
  useEmptyDonorsTrashMutation,
  useGetDonorOverview,
  useUpdateDonorMutation,
};
