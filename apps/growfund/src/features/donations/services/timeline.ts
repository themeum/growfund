import { useInfiniteQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { apiClient } from '@/lib/api';
import { type CommentSchemaType } from '@/schemas/timeline';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { type Activity } from '@/types/activity';

const getTimelines = async (donationId: string, params: BaseQueryParams) => {
  const response = await apiClient.get<PaginatedResponse<Activity>>(
    endpoints.DONATION_ACTIVITIES(donationId),
    {
      params,
    },
  );

  return response.data;
};

const useDonationTimelinesInfiniteQuery = (donationId: string, enabled = true) => {
  return useInfiniteQuery({
    enabled: !!donationId && enabled,
    queryKey: ['Timelines', donationId],
    initialPageParam: 1,
    queryFn: ({ pageParam = 1 }) => getTimelines(donationId, { page: pageParam }),
    getNextPageParam: (response) => {
      const { current_page, has_more } = response;

      return has_more ? current_page + 1 : undefined;
    },
  });
};

const createTimeline = ({
  donation_id,
  ...payload
}: Omit<CommentSchemaType & { donation_id: string }, 'id'>) => {
  return apiClient.post(endpoints.DONATION_TIMELINES(donation_id), {
    ...payload,
    donation_id,
  });
};

const useCreateDonationTimelineMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createTimeline,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Comment created successfully', 'growfund'));
    },
  });
};

const deleteTimeline = ({ donationId, timelineId }: { donationId: string; timelineId: string }) => {
  return apiClient.delete(endpoints.DONATION_TIMELINES_WITH_ID(donationId, timelineId));
};

const useDeleteDonationTimelineMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteTimeline,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Timeline comment deleted', 'growfund'));
    },
  });
};

export {
  useCreateDonationTimelineMutation,
  useDeleteDonationTimelineMutation,
  useDonationTimelinesInfiniteQuery,
};
