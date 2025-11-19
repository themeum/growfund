import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { growfundConfig } from '@/config/growfund';
import { type Payment } from '@/features/settings/features/payments/schemas/payment';
import { apiClient } from '@/lib/api';

const getPaymentGateways = () => {
  return apiClient.get<Payment[]>(endpoints.PAYMENT_GATEWAYS).then((response) => response.data);
};

export const usePaymentGatewaysQuery = () => {
  return useQuery({
    queryKey: ['PaymentGateways'],
    queryFn: getPaymentGateways,
    enabled: growfundConfig.is_pro,
  });
};

const installPaymentGateway = (pluginUrl: string) => {
  return apiClient.post(endpoints.INSTALL_PAYMENT_GATEWAY, {
    plugin_url: pluginUrl,
  });
};

export const useInstallPaymentGateway = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: installPaymentGateway,
    onSuccess() {
      toast.success(__('Payment gateway installed successfully', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['PaymentGateways'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

const storePaymentGateway = ({ name, payload }: { name: string; payload: Payment }) => {
  return apiClient.put(endpoints.PAYMENT_GATEWAY(name), {
    payload: JSON.stringify(payload),
  });
};

export const useStorePaymentGateway = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: storePaymentGateway,
    onSuccess() {
      toast.success(__('Payment gateway configuration updated!', 'growfund'));
      void queryClient.invalidateQueries({ queryKey: ['AppConfig'] });
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};
