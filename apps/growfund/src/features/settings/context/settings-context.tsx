import React, { use } from 'react';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { useManageMultiform } from '@/hooks/use-manage-multiform';

enum AppConfigKeys {
  General = 'growfund_general',
  Campaign = 'growfund_campaign',
  UserAndPermissions = 'growfund_user_permissions',
  Payment = 'growfund_payment',
  Receipt = 'growfund_pdf_receipt',
  EmailAndNotifications = 'growfund_email_and_notifications',
  Security = 'growfund_security',
  Integrations = 'growfund_integrations',
  Branding = 'growfund_branding',
  Advanced = 'growfund_advanced',
  DonationMode = 'growfund_is_donation_mode',
  CurrentUser = 'growfund_current_user',
}

interface SettingsContextType<T extends FieldValues> {
  registerForm: (key: AppConfigKeys, form: UseFormReturn<T>) => () => void;
  getCurrentForm: () => UseFormReturn<T> | null;
  getCurrentKey: () => AppConfigKeys | null;
  isCurrentFormDirty: boolean;
  setIsCurrentFormDirty: (state: boolean) => void;
}

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const SettingsContext = React.createContext<SettingsContextType<any> | null>(null);

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const SettingsProvider = ({ children }: React.ComponentProps<any>) => {
  const multiform = useManageMultiform<FieldValues, AppConfigKeys>();

  return <SettingsContext value={multiform}>{children}</SettingsContext>;
};

const useSettingsContext = <T extends FieldValues = FieldValues>() => {
  const context = use(SettingsContext);

  if (!context) {
    throw new Error('useSettingsContext must be used within a SettingsProvider');
  }

  return context as SettingsContextType<T>;
};

// eslint-disable-next-line react-refresh/only-export-components
export { AppConfigKeys, SettingsProvider, useSettingsContext };
