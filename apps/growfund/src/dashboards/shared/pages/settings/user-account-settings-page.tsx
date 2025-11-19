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
import DeleteUserAccount from '@/dashboards/shared/components/delete-user-account';
import UserResetPasswordDialog from '@/dashboards/shared/components/dialogs/user-reset-password-dialog';
import {
    FormKeys,
    useUserSettingsContext,
} from '@/dashboards/shared/contexts/user-settings-context';
import { type BackerForm, BackerFormSchema } from '@/features/backers/schemas/backer';
import { User as CurrentUser } from '@/utils/user';

const UserAccountSettingsPage = () => {
  const { registerForm, setIsCurrentFormDirty, user } = useUserSettingsContext();

  const form = useForm<BackerForm>({
    resolver: zodResolver(BackerFormSchema),
    defaultValues: {
      is_billing_address_same: CurrentUser.isBacker(),
    },
  });

  useEffect(() => {
    if (user) {
      form.reset.call(null, user as BackerForm);
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
    <div className="growfund-w-full growfund-space-y-3 growfund-mb-10">
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

            {CurrentUser.isBacker() && (
              <>
                <UserFormAddress
                  keyPrefix="shipping_address"
                  label={__('Shipping Address', 'growfund')}
                />
                <UserFormAddress
                  keyPrefix="billing_address"
                  isBillingAddress
                  label={__('Billing Address', 'growfund')}
                />
              </>
            )}

            {CurrentUser.isDonor() && (
              <UserFormAddress
                keyPrefix="billing_address"
                label={__('Billing Address', 'growfund')}
              />
            )}
          </UserFormCardContent>
        </UserFormCard>
      </UserForm>
      <DeleteUserAccount />
    </div>
  );
};

export default UserAccountSettingsPage;
