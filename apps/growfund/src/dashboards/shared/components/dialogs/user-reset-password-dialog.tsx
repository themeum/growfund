import { __ } from '@wordpress/i18n';
import { Lock } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Form } from '@/components/ui/form';
import {
    type ResetPasswordPayload,
    useResetPasswordMutation,
} from '@/dashboards/shared/services/user';
import { type User } from '@/features/settings/schemas/settings';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';

const UserResetPasswordDialog = ({ children, user }: React.PropsWithChildren<{ user: User }>) => {
  const [open, setOpen] = useState(false);
  const form = useForm<ResetPasswordPayload>({
    defaultValues: {
      user_id: user.id,
      current_password: '',
      new_password: '',
      confirm_password: '',
    },
  });

  const resetPasswordMutation = useResetPasswordMutation();

  const { createErrorHandler } = useFormErrorHandler(form);

  const oldPassword = useWatch({ control: form.control, name: 'current_password' });
  const newPassword = useWatch({ control: form.control, name: 'new_password' });
  const confirmNewPassword = useWatch({ control: form.control, name: 'confirm_password' });
  const isNewPasswordValid = newPassword === confirmNewPassword;
  const isDisabled = !oldPassword || !newPassword || !confirmNewPassword;

  useEffect(() => {
    if (isNewPasswordValid) {
      form.clearErrors('confirm_password');
    }
  }, [form, isNewPasswordValid]);

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <Lock className="growfund-size-5 growfund-text-icon-primary" />
            {__('Reset Password', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>
        <Form {...form}>
          <form
            onSubmit={(event) => {
              event.preventDefault();
              if (newPassword && confirmNewPassword && !isNewPasswordValid) {
                form.setError('confirm_password', {
                  message: __('Password and confirm password do not match', 'growfund'),
                });
                return;
              }
              void form.handleSubmit(
                (values) => {
                  resetPasswordMutation.mutate(values, {
                    onError: createErrorHandler(),
                    onSuccess: () => {
                      setOpen(false);
                      form.reset();
                    },
                  });
                },
                (errors) => {
                  console.error(errors);
                },
              )();
            }}
          >
            <div className="growfund-space-y-4 growfund-p-4">
              <TextField
                control={form.control}
                name="current_password"
                type="password"
                label={__('Old Password', 'growfund')}
                placeholder={__('Enter your old password', 'growfund')}
              />
              <div className="growfund-flex growfund-items-end growfund-gap-3">
                <TextField
                  control={form.control}
                  type="password"
                  name="new_password"
                  label={__('Password', 'growfund')}
                  placeholder={__('Enter your new password', 'growfund')}
                />
                <Button
                  variant="outline"
                  onClick={() => {
                    form.setValue('new_password', Math.random().toString(36).substring(2, 15));
                  }}
                >
                  {__('Generate', 'growfund')}
                </Button>
              </div>
              <TextField
                control={form.control}
                type="password"
                name="confirm_password"
                label={__('Confirm Password', 'growfund')}
                placeholder={__('Enter your new password again', 'growfund')}
              />
            </div>
            <DialogFooter>
              <DialogClose asChild>
                <Button variant="outline">{__('Cancel', 'growfund')}</Button>
              </DialogClose>
              <Button type="submit" disabled={isDisabled}>
                {__('Update Password', 'growfund')}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default UserResetPasswordDialog;
