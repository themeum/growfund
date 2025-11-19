import { __ } from '@wordpress/i18n';
import { CheckCircle2, KeyRound, Mail } from 'lucide-react';
import React, { useEffect, useState } from 'react';
import { useFormContext, useWatch, type FieldValues, type UseFormReturn } from 'react-hook-form';

import { CheckWaveIcon } from '@/app/icons';
import { CheckboxField } from '@/components/form/checkbox-field';
import { ComboBoxField } from '@/components/form/combobox-field';
import { TextField } from '@/components/form/text-field';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Form } from '@/components/ui/form';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { useSendResetLinkMutation, useValidateUserEmail } from '@/services/user';
import { createAcronym, generateRandomPassword, isDefined } from '@/utils';
import { countriesAsOptions } from '@/utils/countries';

const UserFormCard = ({
  ref,
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
}) => {
  return (
    <Card ref={ref} className={cn(className)} {...props}>
      {children}
    </Card>
  );
};

UserFormCard.displayName = 'UserFormCard';

const UserFormCardHeader = ({
  ref,
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
}) => {
  const form = useFormContext();
  const { openMediaModal } = useWordpressMedia();
  const handleMediaManager = () => {
    openMediaModal({
      title: __('Select Backer Photo', 'growfund'),
      button_text: __('Select Photo', 'growfund'),
      onSelect: (media) => {
        if (!media.length) return;
        form.setValue('image', media[0], { shouldDirty: true });
      },
    });
  };
  const image = useWatch({ control: form.control, name: 'image' }) as MediaAttachment | undefined;
  const firstName = useWatch({ control: form.control, name: 'first_name' }) as string | undefined;
  const lastName = useWatch({ control: form.control, name: 'last_name' }) as string | undefined;

  const acronym = createAcronym({ first_name: firstName, last_name: lastName });

  return (
    <CardHeader ref={ref} className={cn('growfund-border-b growfund-border-b-border', className)} {...props}>
      <div className="growfund-flex growfund-items-center growfund-gap-6">
        <Avatar className="growfund-size-12">
          <AvatarImage src={image?.url ?? ''} />
          <AvatarFallback>{acronym}</AvatarFallback>
        </Avatar>
        <Button variant="secondary" onClick={handleMediaManager}>
          {isDefined(image) ? __('Change Photo', 'growfund') : __('Upload Photo', 'growfund')}
        </Button>
      </div>
      {children}
    </CardHeader>
  );
};

UserFormCardHeader.displayName = 'UserFormCardHeader';

const UserFormCardContent = ({
  ref,
  className,
  children,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
}) => {
  return (
    <CardContent ref={ref} className={cn('growfund-p-6 growfund-space-y-4', className)} {...props}>
      {children}
    </CardContent>
  );
};

UserFormCardContent.displayName = 'UserFormCardContent';

const UserFormBasics = ({
  ref,
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
}) => {
  const [isValidEmail, setIsValidEmail] = useState(false);

  const form = useFormContext();
  const email = useWatch({ control: form.control, name: 'email' }) as string;
  const validateUserEmailMutation = useValidateUserEmail();

  return (
    <div ref={ref} className={cn('growfund-space-y-4', className)} {...props}>
      <div className="growfund-flex growfund-items-start growfund-gap-5">
        <TextField
          control={form.control}
          name="first_name"
          label={__('First Name', 'growfund')}
          placeholder={__('Enter first name', 'growfund')}
        />
        <TextField
          control={form.control}
          name="last_name"
          label={__('Last Name', 'growfund')}
          placeholder={__('Enter last name', 'growfund')}
        />
      </div>

      <TextField
        control={form.control}
        name="email"
        label={__('Email', 'growfund')}
        labelIcon={
          isValidEmail ? <CheckWaveIcon className="growfund-size-4 growfund-text-icon-success" /> : null
        }
        placeholder={__('Enter email', 'growfund')}
        onBlur={() => {
          if (!form.formState.dirtyFields.email) return;

          setIsValidEmail(false);
          validateUserEmailMutation.mutate(email, {
            onSuccess: () => {
              setIsValidEmail(true);
              form.clearErrors('email');
            },
            onError: (error) => {
              setIsValidEmail(false);
              form.setError('email', {
                type: 'server',
                message: error.message,
              });
            },
          });
        }}
        loading={validateUserEmailMutation.isPending}
      />
      <TextField
        control={form.control}
        name="phone"
        label={__('Phone Number', 'growfund')}
        placeholder={__('Enter phone number', 'growfund')}
      />
    </div>
  );
};

UserFormBasics.displayName = 'UserFormBasics';

const UserFormPassword = ({
  ref,
  className,
  isEditMode,
  sendResetLink = true,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
  isEditMode?: boolean;
  sendResetLink?: boolean;
}) => {
  const [resetPassword, setResetPassword] = useState(false);
  const [showResetLinkSentMessage, setShowResetLinkSentMessage] = useState(false);

  const form = useFormContext();
  const sendResetLinkMutation = useSendResetLinkMutation();

  const showPasswordReset = isEditMode && !resetPassword && sendResetLink;
  const showPasswordResetLink = isEditMode && !resetPassword && sendResetLink;
  const showPasswordFields = !isEditMode || resetPassword;
  const showCancelButton = isEditMode && resetPassword;

  const hasPasswordFieldError = !!form.getFieldState('password').error;

  useEffect(() => {
    if (showResetLinkSentMessage) {
      setTimeout(() => {
        setShowResetLinkSentMessage(false);
      }, 5000);
    }
  }, [showResetLinkSentMessage]);

  return (
    <div ref={ref} className={cn(className)} {...props}>
      <div className="growfund-grid growfund-gap-2">
        {showPasswordReset && (
          <Button
            variant="secondary"
            className="growfund-w-full"
            onClick={() => {
              setResetPassword(true);
            }}
          >
            <KeyRound />
            {__('Reset Password', 'growfund')}
          </Button>
        )}

        {showPasswordResetLink && (
          <Button
            variant="outline"
            className="growfund-w-full"
            onClick={() => {
              sendResetLinkMutation.mutate(undefined, {
                onSuccess() {
                  setShowResetLinkSentMessage(true);
                },
              });
            }}
            loading={sendResetLinkMutation.isPending}
          >
            <Mail />
            {__('Send Reset Link', 'growfund')}
          </Button>
        )}
        {showResetLinkSentMessage && (
          <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-typo-tiny growfund-text-fg-subdued">
            <CheckCircle2 className="growfund-size-4 growfund-text-icon-success" />
            {__('A password reset link was emailed to the user.', 'growfund')}
          </div>
        )}
      </div>

      {showPasswordFields && (
        <div className="growfund-flex growfund-items-center growfund-gap-2">
          <TextField
            control={form.control}
            name="password"
            label={__('Password', 'growfund')}
            placeholder={__('Enter password', 'growfund')}
            type="password"
          />
          <div
            className={cn(
              'growfund-flex growfund-items-center growfund-gap-2 growfund-relative growfund-top-3',
              hasPasswordFieldError && 'growfund-top-[-2px]',
            )}
          >
            <Button
              variant="outline"
              onClick={async () => {
                const password = generateRandomPassword();
                form.setValue('password', password);
                await form.trigger('password');
              }}
            >
              {__('Generate', 'growfund')}
            </Button>

            {showCancelButton && (
              <Button
                variant="secondary"
                onClick={() => {
                  setResetPassword(false);
                }}
              >
                {__('Cancel', 'growfund')}
              </Button>
            )}
          </div>
        </div>
      )}
    </div>
  );
};

UserFormPassword.displayName = 'UserFormPassword';

const UserFormAddress = ({
  ref,
  className,
  keyPrefix,
  label = __('Address', 'growfund'),
  isBillingAddress,
  ...props
}: React.HTMLAttributes<HTMLDivElement> & {
  ref?: React.RefObject<HTMLDivElement>;
  keyPrefix: string;
  label?: string;
  isBillingAddress?: boolean;
}) => {
  const form = useFormContext();

  const isBillingAddressSame = useWatch({
    control: form.control,
    name: 'is_billing_address_same',
  }) as boolean;

  const showSameAsCheckbox = !!isBillingAddress;
  const showAddressFields = !isBillingAddress || !isBillingAddressSame;

  return (
    <div ref={ref} className={cn(className)} {...props}>
      <label className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">{label}</label>
      <div className="growfund-mt-4">
        <div className="growfund-space-y-3">
          {showSameAsCheckbox && (
            <CheckboxField
              control={form.control}
              name="is_billing_address_same"
              label={__('Same as shipping address', 'growfund')}
            />
          )}

          {showAddressFields && (
            <>
              <TextField
                control={form.control}
                name={`${keyPrefix}.address` as const}
                placeholder={__('Address line 1', 'growfund')}
              />
              <TextField
                control={form.control}
                name={`${keyPrefix}.address_2` as const}
                placeholder={__('Address line 2 (Optional)', 'growfund')}
              />

              <div className="growfund-flex growfund-items-start growfund-gap-3">
                <TextField
                  control={form.control}
                  name={`${keyPrefix}.city` as const}
                  placeholder={__('City', 'growfund')}
                />
                <TextField
                  control={form.control}
                  name={`${keyPrefix}.zip_code` as const}
                  placeholder={__('Zip Code', 'growfund')}
                />
              </div>

              <ComboBoxField
                control={form.control}
                name={`${keyPrefix}.country` as const}
                placeholder={__('Select country', 'growfund')}
                options={countriesAsOptions()}
              />
            </>
          )}
        </div>
      </div>
    </div>
  );
};

UserFormAddress.displayName = 'UserFormAddress';

interface UserFormProps<TFields extends FieldValues>
  extends Omit<React.HTMLAttributes<HTMLFormElement>, 'onSubmit'> {
  ref?: React.RefObject<HTMLFormElement | null>;
  form: UseFormReturn<TFields>;
  onSubmit?: (data: TFields) => void;
}

const UserForm = <TFields extends FieldValues>({
  ref,
  className,
  form,
  children,
  onSubmit,
  ...props
}: UserFormProps<TFields>) => {
  return (
    <Form {...form}>
      <form
        ref={ref}
        className={cn(className)}
        {...props}
        onSubmit={
          onSubmit
            ? form.handleSubmit(onSubmit, (errors) => {
                console.error(errors);
              })
            : undefined
        }
      >
        {children}
      </form>
    </Form>
  );
};

export {
    UserForm,
    UserFormAddress,
    UserFormBasics,
    UserFormCard,
    UserFormCardContent,
    UserFormCardHeader,
    UserFormPassword
};

