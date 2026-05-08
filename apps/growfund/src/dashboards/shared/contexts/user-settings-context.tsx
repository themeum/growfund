import React, { use, useMemo } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { type User } from '@/features/settings/schemas/settings';
import { useManageMultiform } from '@/hooks/use-manage-multiform';
import { useCurrentUserQuery } from '@/services/user';

// eslint-disable-next-line react-refresh/only-export-components
export enum FormKeys {
  Account = 'growfund_user_account_settings',
  Notifications = 'growfund_user_notifications_settings',
  Payout = 'growfund_user_payout_settings',
}

interface UserSettingsContextType<T extends FieldValues = FieldValues> {
  registerForm: (key: FormKeys, form: UseFormReturn<T>) => () => void;
  getCurrentForm: () => UseFormReturn<T> | null;
  getCurrentKey: () => FormKeys | null;
  isCurrentFormDirty: boolean;
  setIsCurrentFormDirty: (state: boolean) => void;
  user: User | null;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const UserSettingsContext = React.createContext<UserSettingsContextType<any> | null>(null);

const useUserSettingsContext = () => {
  const context = use(UserSettingsContext);
  if (!context) {
    throw new Error('useUserSettingsContext must be used within a UserSettingsContext');
  }
  return context;
};

const UserSettingsProvider = <T extends FieldValues = FieldValues>({
  children,
}: React.PropsWithChildren<T>) => {
  const multiform = useManageMultiform<FieldValues, FormKeys>();
  const currentUserQuery = useCurrentUserQuery();

  const user = useMemo(() => {
    if (!currentUserQuery.data) return null;
    return currentUserQuery.data;
  }, [currentUserQuery.data]);

  return (
    <UserSettingsContext.Provider value={{ ...multiform, user }}>
      {children}
    </UserSettingsContext.Provider>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { UserSettingsProvider, useUserSettingsContext };
