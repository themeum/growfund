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
import {
  type Backer,
  type BackerOverview,
  type BackerPayload,
} from '@/features/backers/schemas/backer';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { type Activity } from '@/types/activity';
import { assertIsDefined, toPaginatedQueryParams } from '@/utils';

const getBackerOverview = (id: string) => {
  return apiClient
    .get<BackerOverview>(endpoints.BACKERS_OVERVIEW(id))
    .then((response) => response.data);
};

const useGetBackerOverview = (id: string) => {
  return useQuery({
    queryKey: ['BackerOverview', id],
    queryFn: () => getBackerOverview(id),
    enabled: !!id,
  });
};

interface BackersQueryParams extends BaseQueryParams {
  search?: string;
  status?: string;
  campaign_id?: string;
}

const getBackers = async (params: BackersQueryParams) => {
  return apiClient
    .get<PaginatedResponse<Backer>>(endpoints.BACKERS, { params: toPaginatedQueryParams(params) })
    .then((response) => response.data);
};

const useBackersQuery = (params: BackersQueryParams) => {
  return useQuery({
    queryKey: ['Backers', params],
    queryFn: () => getBackers(params),
    placeholderData: keepPreviousData,
  });
};

const createBacker = async (payload: BackerPayload) => {
  return apiClient.post(endpoints.BACKERS, payload);
};

const useCreateBackerMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createBacker,
    onSuccess() {
      toast.success(__('Backer created successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Backers'] });
    },
  });
};

const updateBacker = ({ id, ...payload }: BackerPayload & { id: string }) => {
  return apiClient.put(endpoints.BACKERS_WITH_ID(id), payload);
};

const useUpdateBackerMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateBacker,
    onSuccess() {
      toast.success(__('Backer updated successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Backers'] });
      void queryClient.invalidateQueries({ queryKey: ['BackerOverview'] });
      void queryClient.invalidateQueries({ queryKey: ['CurrentUser'] });
    },
  });
};

const deleteBacker = async (id: string) => {
  return apiClient.delete(endpoints.BACKERS_WITH_ID(id));
};

const useDeleteBackerMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteBacker,
    onSuccess() {
      toast.success(__('Backer deleted successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Backers'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};
const bulkActions = (payload: {
  ids: string[];
  is_permanent_delete?: boolean;
  action: 'delete' | 'trash' | 'restore';
}) => {
  return apiClient.patch<{ message: string }>(endpoints.BACKERS_BULK_ACTIONS, payload);
};

const useBackerBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkActions,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Backers'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};
const emptyBackersTrash = (payload: { delete_associated_pledges: boolean }) => {
  return apiClient.delete(endpoints.EMPTY_BACKERS_TRASH, {
    data: payload,
  });
};

const useEmptyBackersTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyBackersTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Backers'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

interface CampaignsByBackerQueryParams extends BaseQueryParams {
  backer_id: string | undefined;
  search?: string;
  start_date?: string;
  end_date?: string;
}

const getCampaignsByBacker = async ({
  backer_id,
  ...params
}: Omit<CampaignsByBackerQueryParams, 'backer_id'> & { backer_id: string }) => {
  return apiClient
    .get<PaginatedResponse<Campaign>>(endpoints.BACKERS_CAMPAIGNS_BY_A_BACKER(backer_id), {
      params: toPaginatedQueryParams(params),
    })
    .then((response) => response.data);
};

const useCampaignsByBackerQuery = (params: CampaignsByBackerQueryParams) => {
  return useQuery({
    queryKey: ['CampaignsByABacker', params],
    enabled: !!params.backer_id,
    queryFn: () => {
      assertIsDefined(params.backer_id, 'Backer ID is required');
      return getCampaignsByBacker(
        params as Omit<CampaignsByBackerQueryParams, 'backer_id'> & { backer_id: string },
      );
    },
    placeholderData: keepPreviousData,
  });
};

interface BackerGivingStats {
  pledged_amount: number;
  total_pledges: number;
  backed_amount: number;
  backed_campaigns: number;
}

const getBackerGivingStats = (id: string) => {
  return apiClient
    .get<BackerGivingStats>(endpoints.BACKER_GIVING_STATS(id))
    .then((response) => response.data);
};

const useBackerGivingStatsQuery = (id: string | undefined) => {
  return useQuery({
    queryKey: ['BackerGivingStats', id],
    enabled: !!id,
    queryFn: () => {
      assertIsDefined(id, 'Backer ID is required');
      return getBackerGivingStats(id);
    },
  });
};

const getBackerActivities = async (backerId: string, params: BaseQueryParams) => {
  const response = await apiClient.get<PaginatedResponse<Activity>>(
    endpoints.BACKER_ACTIVITIES(backerId),
    {
      params,
    },
  );

  return response.data;
};

const useBackerActivitiesInfiniteQuery = (backerId: string, enabled = true) => {
  return useInfiniteQuery({
    enabled: !!backerId && enabled,
    queryKey: ['BackerActivities', backerId],
    initialPageParam: 1,
    queryFn: ({ pageParam = 1 }) => getBackerActivities(backerId, { page: pageParam }),
    getNextPageParam: (response) => {
      const { current_page, has_more } = response;

      return has_more ? current_page + 1 : undefined;
    },
  });
};

export {
  useBackerActivitiesInfiniteQuery,
  useBackerBulkActionsMutation,
  useBackerGivingStatsQuery,
  useBackersQuery,
  useCampaignsByBackerQuery,
  useCreateBackerMutation,
  useDeleteBackerMutation,
  useEmptyBackersTrashMutation,
  useGetBackerOverview,
  useUpdateBackerMutation,
};
