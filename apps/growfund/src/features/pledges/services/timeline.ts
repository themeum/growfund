import { useInfiniteQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { apiClient } from '@/lib/api';
import { type CommentSchemaType } from '@/schemas/timeline';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { type Activity } from '@/types/activity';

const getTimelines = async (pledgerId: string, params: BaseQueryParams) => {
  const response = await apiClient.get<PaginatedResponse<Activity>>(
    endpoints.PLEDGE_ACTIVITIES(pledgerId),
    {
      params,
    },
  );

  return response.data;
};

const useTimelinesInfiniteQuery = (pledgerId: string, enabled = true) => {
  return useInfiniteQuery({
    enabled: !!pledgerId && enabled,
    queryKey: ['Timelines', pledgerId],
    initialPageParam: 1,
    queryFn: ({ pageParam = 1 }) => getTimelines(pledgerId, { page: pageParam }),
    getNextPageParam: (response) => {
      const { current_page, has_more } = response;

      return has_more ? current_page + 1 : undefined;
    },
  });
};

const createTimeline = ({
  pledge_id,
  ...payload
}: Omit<CommentSchemaType & { pledge_id: string }, 'id'>) => {
  return apiClient.post(endpoints.PLEDGE_TIMELINES(pledge_id), {
    ...payload,
    pledge_id,
  });
};

const useCreateTimelineMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createTimeline,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success(__('Comment created successfully', 'growfund'));
    },
  });
};

const deleteTimeline = ({ pledgeId, timelineId }: { pledgeId: string; timelineId: string }) => {
  return apiClient.delete(endpoints.PLEDGE_TIMELINES_WITH_ID(pledgeId, timelineId));
};

const useDeleteTimelineMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deleteTimeline,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['Timelines'] });
      toast.success('Timeline comment deleted');
    },
  });
};

export { useCreateTimelineMutation, useDeleteTimelineMutation, useTimelinesInfiniteQuery };
