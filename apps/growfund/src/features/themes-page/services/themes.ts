import { useQuery } from '@tanstack/react-query';

import { theme } from '@/features/themes-page/data/themes';
export const useAllThemesQuery = () => {
  return useQuery({
    queryKey: ['AllThemes'],
    queryFn: () => Promise.resolve(theme),
  });
};
