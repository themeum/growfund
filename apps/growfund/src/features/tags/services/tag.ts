import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Tag, type TagPayload } from '@/features/tags/schemas/tag';
import { apiClient } from '@/lib/api';

const getTags = () => {
  return apiClient.get<Tag[]>(endpoints.TAGS).then((response) => response.data);
};

const useGetTagsQuery = () => {
  return useQuery({
    queryKey: ['Tags'],
    queryFn: getTags,
  });
};

const createTag = (payload: TagPayload) => {
  return apiClient.post<Tag>(endpoints.TAGS, payload).then((response) => response.data);
};

const useCreateTagMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createTag,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['Tags'] });
      toast.success(__('Tag created successfully', 'growfund'));
    },
  });
};

const updateTag = ({ id, ...payload }: TagPayload & { id: string }) => {
  return apiClient.put<Tag>(endpoints.TAGS_WITH_ID(id), payload).then((response) => response.data);
};

const useUpdateTagMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateTag,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Tags'] });
      toast.success(__('Tag updated successfully', 'growfund'));
    },
  });
};

const deleteTag = (id: string) => {
  return apiClient.delete(endpoints.TAGS_WITH_ID(id));
};

const useDeleteTagMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteTag,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Tags'] });
      toast.success(__('Tags deleted successfully', 'growfund'));
    },
  });
};

interface BulkActionPayload {
  action: 'delete';
  ids: string[];
}

const tagsBulkActions = (payload: BulkActionPayload) => {
  return apiClient.patch(endpoints.TAGS_BULK_ACTIONS, payload);
};

const useTagsBulkActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: tagsBulkActions,
    onSuccess(data) {
      void queryClient.invalidateQueries({ queryKey: ['Tags'] });
      toast.success((data as unknown as { message: string }).message);
    },
  });
};

export {
  useCreateTagMutation,
  useDeleteTagMutation,
  useGetTagsQuery,
  useTagsBulkActionsMutation,
  useUpdateTagMutation,
};
