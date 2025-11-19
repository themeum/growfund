import { useQuery } from '@tanstack/react-query';

import { endpoints } from '@/config/endpoints';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import { apiClient } from '@/lib/api';

const getPaymentMethods = (type: 'online-payment' | 'manual-payment' | 'all') => {
  return apiClient
    .get<Payment[]>(endpoints.PAYMENT_METHODS(type))
    .then((response) => response.data);
};

const useGetPaymentMethodsQuery = ({
  type,
}: {
  type: 'online-payment' | 'manual-payment' | 'all';
}) => {
  return useQuery({
    queryKey: ['GetPaymentMethods', type],
    queryFn: () => {
      return getPaymentMethods(type);
    },
  });
};

export { useGetPaymentMethodsQuery };
