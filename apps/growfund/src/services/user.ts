import { useMutation, useQuery } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { type User } from '@/features/settings/schemas/settings';
import { apiClient } from '@/lib/api';

const validateUserEmail = (email: string) => {
  return apiClient.post<boolean>(endpoints.VALIDATE_EMAIL, {
    email,
  });
};

const useValidateUserEmail = () => {
  return useMutation({
    mutationFn: validateUserEmail,
  });
};

const sendResetLink = () => {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve(true);
    }, 1000);
  });
};

const useSendResetLinkMutation = () => {
  return useMutation({
    mutationFn: sendResetLink,
    onError(error) {
      toast.error(error.message);
    },
  });
};

const getCurrentUser = () => {
  return apiClient.get<User>(endpoints.CURRENT_USER).then((response) => response.data);
};

const useCurrentUserQuery = () => {
  return useQuery({
    queryKey: ['CurrentUser'],
    queryFn: getCurrentUser,
  });
};

interface UserNotificationsPayload {
  id: string;
  [key: string]: string | boolean;
}

const updateUserNotifications = ({ id, ...payload }: UserNotificationsPayload) => {
  return apiClient.patch(endpoints.USER_NOTIFICATIONS(id), payload);
};

const useUpdateUserNotificationsMutation = () => {
  return useMutation({
    mutationFn: updateUserNotifications,
    onSuccess() {
      toast.success(__('Notification settings updated successfully', 'growfund'));
    },
    onError(error) {
      toast.error(error.message);
    },
  });
};

export {
  useCurrentUserQuery,
  useSendResetLinkMutation,
  useUpdateUserNotificationsMutation,
  useValidateUserEmail,
};
