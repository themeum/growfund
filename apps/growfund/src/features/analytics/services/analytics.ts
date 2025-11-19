import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type AnalyticType } from '@/features/analytics/schemas/analytics';
import { apiClient } from '@/lib/api';

interface QueryParams {
  start_date?: string;
  end_date?: string;
  campaign_id?: string;
}
const getAnalytics = <T>(type: AnalyticType, params?: QueryParams) => {
  return apiClient
    .get<T>(endpoints.ANALYTICS(type), {
      params,
    })
    .then((response) => response.data);
};

const useAnalyticsQuery = <T>(type: AnalyticType, params?: QueryParams) => {
  return useQuery({
    queryKey: ['Analytics', type, params],
    queryFn: () => getAnalytics<T>(type, params),
  });
};

export { useAnalyticsQuery };
