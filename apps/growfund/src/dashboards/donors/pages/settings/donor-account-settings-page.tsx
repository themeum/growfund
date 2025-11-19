import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { KeyRound } from 'lucide-react';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import {
    UserForm,
    UserFormAddress,
    UserFormBasics,
    UserFormCard,
    UserFormCardContent,
    UserFormCardHeader,
} from '@/components/user-form/user-form';
import UserResetPasswordDialog from '@/dashboards/shared/components/dialogs/user-reset-password-dialog';
import {
    FormKeys,
    useUserSettingsContext,
} from '@/dashboards/shared/contexts/user-settings-context';
import { type DonorForm, DonorFormSchema } from '@/features/donors/schemas/donor';

const DonorAccountSettingsPage = () => {
  const { registerForm, setIsCurrentFormDirty, user } = useUserSettingsContext();

  const form = useForm<DonorForm>({
    resolver: zodResolver(DonorFormSchema),
  });

  useEffect(() => {
    if (user) {
      form.reset.call(null, user as unknown as DonorForm);
    }
  }, [user, form.reset]);

  useEffect(() => {
    const cleanup = registerForm(FormKeys.Account, form);
    return () => {
      cleanup();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [registerForm]);

  useEffect(() => {
    setIsCurrentFormDirty(form.formState.isDirty);
  }, [form.formState.isDirty, setIsCurrentFormDirty]);

  if (!user) {
    return null;
  }

  return (
    <div className="growfund-w-full growfund-space-y-3">
      <p className="growfund-typo-small growfund-font-semibold growfund-text-fg-primary growfund-mt-2">
        {__('Account', 'growfund')}
      </p>

      <UserForm form={form}>
        <UserFormCard>
          <UserFormCardHeader />
          <UserFormCardContent>
            <UserFormBasics />

            <UserResetPasswordDialog user={user}>
              <Button variant="secondary" className="growfund-w-full">
                <KeyRound />
                {__('Reset Password', 'growfund')}
              </Button>
            </UserResetPasswordDialog>

            <UserFormAddress
              keyPrefix="billing_address"
              label={__('Billing Address', 'growfund')}
            />
          </UserFormCardContent>
        </UserFormCard>
      </UserForm>
    </div>
  );
};

export default DonorAccountSettingsPage;
