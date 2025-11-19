import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { apiClient } from '@/lib/api';
import { type WPPage } from '@/schemas/wp-page';

const getWordPressPages = () => {
  return apiClient.get<WPPage[]>(endpoints.WORDPRESS_PAGES).then((response) => response.data);
};

const useWordPressPagesQuery = () => {
  return useQuery({
    queryKey: ['WordPressPages'],
    queryFn: getWordPressPages,
  });
};

export { useWordPressPagesQuery };
