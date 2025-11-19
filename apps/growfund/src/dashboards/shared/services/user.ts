import { useMutation } from '@tanstack/react-query';
import { toast } from 'sonner';

import { endpoints } from '@/config/endpoints';
import { growfundConfig } from '@/config/growfund';
import { apiClient } from '@/lib/api';

export interface ResetPasswordPayload {
  current_password: string;
  new_password: string;
  confirm_password: string;
  user_id: string;
}

const resetPassword = ({ user_id, ...payload }: ResetPasswordPayload) => {
  return apiClient.post(endpoints.USER_RESET_PASSWORD(user_id), payload);
};

const useResetPasswordMutation = () => {
  return useMutation({
    mutationFn: resetPassword,
    onSuccess: () => {
      toast.success('Password reset successfully');
    },
  });
};

const logout = () => {
  return apiClient.post(endpoints.USER_LOGOUT);
};

const useLogoutMutation = () => {
  return useMutation({
    mutationFn: logout,
    onSuccess: () => {
      window.location.href = growfundConfig.site_url;
    },
    onError: () => {
      toast.error('Failed to logout.');
    },
  });
};

const deleteMyAccount = () => {
  return apiClient.delete(endpoints.DELETE_MY_ACCOUNT);
};

const useDeleteMyAccountMutation = () => {
  return useMutation({
    mutationFn: deleteMyAccount,
    onSuccess: () => {
      window.location.href = growfundConfig.site_url;
    },
    onError: () => {
      toast.error('Failed to delete your account.');
    },
  });
};

export { useDeleteMyAccountMutation, useLogoutMutation, useResetPasswordMutation };
