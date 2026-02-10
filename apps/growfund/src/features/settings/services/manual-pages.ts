import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { apiClient } from '@/lib/api';
import { type WPPage } from '@/schemas/wp-page';

interface RegenerateResponse {
  message: string;
  success?: boolean;
}

const getManualPages = () => {
  return apiClient.get<WPPage[]>(endpoints.MANUAL_PAGES).then((response) => response.data);
};

export const useGetManualPagesQuery = () => {
  return useQuery({
    queryKey: ['ManualPages'],
    queryFn: getManualPages,
  });
};

const regeneratePages = () => {
  return apiClient
    .post<RegenerateResponse>(endpoints.REGENERATE_PAGES, {})
    .then((response) => response.data);
};

export const useRegeneratePagesMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: regeneratePages,
    onSuccess: () => {
      toast.success('Pages re-generated successfully');
      void queryClient.invalidateQueries({ queryKey: ['ManualPages'] });
      void queryClient.invalidateQueries({ queryKey: ['AppConfig'] });
    },
    onError: (error) => {
      toast.error(error.message);
    },
  });
};
