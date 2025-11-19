import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import {
  type CampaignMode,
  type PaymentMode,
} from '@/features/onboarding/contexts/onboarding-context';
import { type AppConfigKeys } from '@/features/settings/context/settings-context';
import {
  type EmailContent,
  type EmailContentPayload,
} from '@/features/settings/schemas/email-content';
import { type AppConfig } from '@/features/settings/schemas/settings';
import { apiClient } from '@/lib/api';

const getAppConfig = () => {
  return apiClient.get<AppConfig>(endpoints.APP_CONFIG).then((response) => response.data);
};

const useAppConfigQuery = () => {
  return useQuery({
    queryKey: ['AppConfig'],
    queryFn: getAppConfig,
  });
};

interface Payload {
  key: AppConfigKeys;
  data: string;
}

const storeAppConfig = (payload: Payload) => {
  return apiClient.post(endpoints.APP_CONFIG, payload);
};

const useStoreAppConfigMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: storeAppConfig,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['AppConfig'] });
    },
    onError: (error) => {
      toast.error(__('Failed to save', 'growfund'), {
        description: error.message,
      });
    },
  });
};

const getOption = (key: string) => {
  return apiClient
    .get<unknown>(endpoints.OPTIONS, {
      params: { key },
    })
    .then((response) => response.data);
};

const useGetOptionQuery = (key: string, enabled = true) => {
  return useQuery({
    queryKey: ['GlobalOption', key],
    queryFn: () => getOption(key),
    enabled,
  });
};

const storeEmailContent = (payload: { key: string; data: EmailContentPayload }) => {
  return apiClient.post(endpoints.EMAIL_CONTENT, payload);
};

const useStoreEmailContentMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: storeEmailContent,
    onError(error) {
      toast.error(error.message);
    },
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['EmailContent'] });
    },
  });
};

const restoreEmailContent = (payload: { key: string }) => {
  return apiClient.post(endpoints.EMAIL_CONTENT_RESTORE, payload);
};

const useRestoreEmailContentMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: restoreEmailContent,
    onError(error) {
      toast.error(error.message);
    },
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['EmailContent'] });
    },
  });
};

const storeOption = (payload: { key: string; data: unknown }) => {
  return apiClient.post(endpoints.OPTIONS, payload);
};

const useStoreOptionMutation = () => {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: storeOption,
    onError(error) {
      toast.error(error.message);
    },
    onSuccess() {
      void queryClient.invalidateQueries({ queryKey: ['GlobalOption'] });
      void queryClient.invalidateQueries({ queryKey: ['EmailContent'] });
    },
  });
};

const getEmailContent = (key: string) => {
  return apiClient
    .get<EmailContent>(endpoints.EMAIL_CONTENT, {
      params: { key },
    })
    .then((res) => res.data);
};
const useGetEmailContentQuery = (key: string) => {
  return useQuery({
    queryKey: ['EmailContent', key],
    queryFn: () => getEmailContent(key),
    enabled: !!key,
  });
};

interface OnboardingPayload {
  campaign_mode: CampaignMode;
  payment_mode: PaymentMode;
  base_country: string;
  currency: string;
}

const handleOnboarding = (payload: OnboardingPayload) => {
  return apiClient.post(endpoints.ONBOARDING, payload);
};

const useOnboardingMutation = (isMigrating = false) => {
  return useMutation({
    mutationFn: handleOnboarding,
    onSuccess() {
      if (!isMigrating) {
        toast.success(__('Onboarding completed!', 'growfund'));
      }
    },
    onError(error) {
      toast.error(__('Failed to complete onboarding!', 'growfund'), {
        description: error.message,
      });
    },
  });
};

export {
  useAppConfigQuery,
  useGetEmailContentQuery,
  useGetOptionQuery,
  useOnboardingMutation,
  useRestoreEmailContentMutation,
  useStoreAppConfigMutation,
  useStoreEmailContentMutation,
  useStoreOptionMutation,
};
