import { useMutation } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { apiClient } from '@/lib/api';
import { type MediaAttachment } from '@/schemas/media';

const uploadMedia = async (files: File[]) => {
  const formData = new FormData();

  files.forEach((file) => {
    formData.append('media[]', file);
  });

  return apiClient.post<{ media: MediaAttachment[] }>(endpoints.UPLOAD_MEDIA, formData);
};

const useMediaUploadMutation = () => {
  return useMutation({
    mutationFn: uploadMedia,
  });
};

export { useMediaUploadMutation };
