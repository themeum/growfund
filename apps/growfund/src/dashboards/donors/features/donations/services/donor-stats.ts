import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type DonorStats } from '@/dashboards/donors/features/donations/schemas/donor-stats';
import { apiClient } from '@/lib/api';

const getDonorStats = () => {
  return apiClient.get<DonorStats>(endpoints.DONOR_STATS).then((response) => response.data);
};

const useGetDonorStats = () => {
  return useQuery({
    queryKey: ['DonorStats'],
    queryFn: () => getDonorStats(),
  });
};

export { useGetDonorStats };
