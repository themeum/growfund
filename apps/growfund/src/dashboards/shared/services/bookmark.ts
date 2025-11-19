import { keepPreviousData, useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { apiClient } from '@/lib/api';
import { type BaseQueryParams, type PaginatedResponse } from '@/types';
import { assertIsDefined } from '@/utils';

interface BookmarksQueryParams extends BaseQueryParams {
  user_id: string | undefined;
  page: number;
  search?: string;
}

const getBookmarks = (params: BookmarksQueryParams) => {
  return apiClient
    .get<PaginatedResponse<Campaign>>(endpoints.BOOKMARKS, {
      params,
    })
    .then((response) => response.data);
};

const useBookmarksQuery = (params: BookmarksQueryParams) => {
  return useQuery({
    queryKey: ['Bookmarks', params],
    enabled: !!params.user_id,
    queryFn: () => {
      assertIsDefined(params.user_id, 'user_id is required');
      return getBookmarks(params);
    },
    placeholderData: keepPreviousData,
  });
};

const removeBookmark = ({ user_id, campaign_id }: { user_id: string; campaign_id: string }) => {
  return apiClient.post(endpoints.BOOKMARKS, { user_id, campaign_id });
};

const useRemoveBookmarkMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: removeBookmark,
    onSuccess: () => {
      toast.success('Bookmark removed successfully');
      void queryClient.invalidateQueries({ queryKey: ['Bookmarks'] });
    },
    onError: (error) => {
      toast.error(error.message);
    },
  });
};

export { useBookmarksQuery, useRemoveBookmarkMutation };
