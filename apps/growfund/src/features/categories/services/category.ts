import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import {
  CategoryResponseSchema,
  type Category,
  type CategoryPayload,
} from '@/features/categories/schemas/category';
import { apiClient } from '@/lib/api';
import { assertIsDefined } from '@/utils';
import { parseResponse } from '@/utils/response';

const getTopLevelCategories = () => {
  return apiClient
    .get<Category[]>(endpoints.TOP_LEVEL_CATEGORIES)
    .then((response) => parseResponse(response.data, CategoryResponseSchema));
};

const useGetTopLevelCategoriesQuery = () => {
  return useQuery({
    queryKey: ['TopLevelCategories'],
    queryFn: getTopLevelCategories,
  });
};

const getSubCategories = (parentId: string) => {
  return apiClient
    .get<Category[]>(endpoints.SUBCATEGORIES(parentId))
    .then((response) => parseResponse(response.data, CategoryResponseSchema));
};

const useGetSubCategoriesQuery = (parentId: string | undefined) => {
  return useQuery({
    queryKey: ['SubCategories', parentId],
    enabled: !!Number(parentId),
    queryFn: () => {
      assertIsDefined(parentId, 'Parent ID is required');
      return getSubCategories(parentId);
    },
  });
};

const getCategories = () => {
  return apiClient
    .get<Category[]>(endpoints.CATEGORIES)
    .then((response) => parseResponse(response.data, CategoryResponseSchema));
};

const useGetCategoriesQuery = () => {
  return useQuery({
    queryKey: ['Categories'],
    queryFn: getCategories,
  });
};

const createCategory = (payload: CategoryPayload) => {
  return apiClient.post(endpoints.CATEGORIES, payload);
};

const useCreateCategoryMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: createCategory,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Categories'] });
      void queryClient.invalidateQueries({ queryKey: ['TopLevelCategories'] });
      void queryClient.invalidateQueries({ queryKey: ['SubCategories'] });
      toast.success(__('Category created successfully', 'growfund'));
    },
  });
};

const updateCategory = ({ id, ...payload }: CategoryPayload & { id: string }) => {
  return apiClient.put(endpoints.CATEGORIES_WITH_ID(id), payload);
};

const useUpdateCategoryMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: updateCategory,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Categories'] });
      void queryClient.invalidateQueries({ queryKey: ['TopLevelCategories'] });
      void queryClient.invalidateQueries({ queryKey: ['SubCategories'] });
      toast.success(__('Category updated successfully', 'growfund'));
    },
  });
};

const deleteCategory = (id: string) => {
  return apiClient.delete(endpoints.CATEGORIES_WITH_ID(id));
};

const useDeleteCategoryMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: deleteCategory,
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['Categories'] });
      void queryClient.invalidateQueries({ queryKey: ['TopLevelCategories'] });
      void queryClient.invalidateQueries({ queryKey: ['SubCategories'] });
      toast.success(__('Category deleted successfully', 'growfund'));
    },
  });
};

const bulkCategoryActions = (payload: { action: 'delete'; ids: string[] }) => {
  return apiClient.patch(endpoints.CATEGORIES_BULK_ACTIONS, payload);
};

const useBulkCategoryActionsMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: bulkCategoryActions,
    onSuccess(data) {
      void queryClient.invalidateQueries({ queryKey: ['Categories'] });
      void queryClient.invalidateQueries({ queryKey: ['TopLevelCategories'] });
      void queryClient.invalidateQueries({ queryKey: ['SubCategories'] });
      toast.success((data as unknown as { message: string }).message);
    },
  });
};

const emptyCategoriesTrash = () => {
  return apiClient.delete(endpoints.EMPTY_CATEGORIES_TRASH);
};

const useEmptyCategoriesTrashMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: emptyCategoriesTrash,
    onSuccess() {
      toast.success(__('Trash emptied successfully.', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['Categories'] });
      void queryClient.invalidateQueries({ queryKey: ['TopLevelCategories'] });
      void queryClient.invalidateQueries({ queryKey: ['SubCategories'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

export {
  useBulkCategoryActionsMutation,
  useCreateCategoryMutation,
  useDeleteCategoryMutation,
  useEmptyCategoriesTrashMutation,
  useGetCategoriesQuery,
  useGetSubCategoriesQuery,
  useGetTopLevelCategoriesQuery,
  useUpdateCategoryMutation,
};
