import { zodResolver } from '@hookform/resolvers/zod';
import { EnvelopeClosedIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import React, { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import { SelectField } from '@/components/form/select-field';
import { SwitchField } from '@/components/form/switch-field';
import { TextField } from '@/components/form/text-field';
import { Box, BoxContent } from '@/components/ui/box';
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
import { MailServerSchema, type MailServerForm } from '@/features/settings/schemas/settings';

interface EmailConfigDialogProps {
  defaultValues?: MailServerForm;
  onValueChange?: (values: MailServerForm) => void;
  open: boolean;
  onOpenChange: (value: boolean) => void;
}

const EmailConfigDialog = ({
  children,
  defaultValues,
  onValueChange,
  open,
  onOpenChange,
}: React.PropsWithChildren<EmailConfigDialogProps>) => {
  const form = useForm<MailServerForm>({
    resolver: zodResolver(MailServerSchema),
  });

  useEffect(() => {
    form.reset.call(null, { enable_authentication: true, ...defaultValues });
  }, [defaultValues, form.reset]);

  const mailer = useWatch({ control: form.control, name: 'mailer' });
  const enable_authentication = useWatch({
    control: form.control,
    name: 'enable_authentication',
  });

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent>
        <form
          onSubmit={form.handleSubmit(
            (values) => {
              onValueChange?.(values);
              onOpenChange(false);
            },
            (errors) => {
              console.error(errors);
            },
          )}
        >
          <Form {...form}>
            <DialogHeader>
              <DialogTitle className="growfund-flex growfund-items-center growfund-gap-3">
                <EnvelopeClosedIcon className="growfund-size-5" />
                {__('Mail Server Configuration', 'growfund')}
              </DialogTitle>
              <DialogCloseButton />
            </DialogHeader>
            <div className="growfund-mt-4">
              <Box className="growfund-m-4 growfund-mt-0 growfund-border-none growfund-shadow-none growfund-h-[clamp(20rem,60svh,30rem)] growfund-overflow-y-auto">
                <BoxContent className="growfund-space-y-4">
                  <TextField
                    control={form.control}
                    name="from_email"
                    label={__('From email', 'growfund')}
                    placeholder={__('Enter the from email', 'growfund')}
                  />
                  <TextField
                    control={form.control}
                    name="from_name"
                    label={__('From name', 'growfund')}
                    placeholder={__('Enter the from name', 'growfund')}
                  />
                  <SelectField
                    control={form.control}
                    name="mailer"
                    options={[
                      { label: __('PHP Mail', 'growfund'), value: 'php-mail' },
                      { label: __('SMTP', 'growfund'), value: 'smtp' },
                    ]}
                    label={__('Mailer', 'growfund')}
                  />

                  {mailer === 'smtp' && (
                    <>
                      <TextField
                        control={form.control}
                        name="host"
                        label={__('Host', 'growfund')}
                        placeholder={__('Enter the smtp host', 'growfund')}
                      />
                      <TextField
                        control={form.control}
                        name="port"
                        label={__('Port', 'growfund')}
                        placeholder={__('Enter the smtp port', 'growfund')}
                        description={__(
                          'Common ports: 25 (unencrypted), 465 (SSL/TLS), 587 (STARTTLS), 2525 (alternative).',
                          'growfund',
                        )}
                      />
                      <SelectField
                        control={form.control}
                        name="encryption"
                        options={[
                          {
                            label: __('None', 'growfund'),
                            value: 'none',
                          },
                          {
                            label: __('SMTPS', 'growfund'),
                            value: 'ssl',
                          },
                          {
                            label: __('STARTTLS', 'growfund'),
                            value: 'tls',
                          },
                        ]}
                        label={__('Encryption', 'growfund')}
                      />

                      <SwitchField
                        control={form.control}
                        name="enable_authentication"
                        label={__('Enable authentication', 'growfund')}
                      />

                      {enable_authentication && (
                        <>
                          <TextField
                            control={form.control}
                            name="username"
                            label={__('Username', 'growfund')}
                            placeholder={__('Enter the smtp username', 'growfund')}
                          />
                          <TextField
                            control={form.control}
                            name="password"
                            type="password"
                            label={__('Password', 'growfund')}
                            placeholder={__('Enter the smtp password', 'growfund')}
                          />
                        </>
                      )}
                    </>
                  )}
                </BoxContent>
              </Box>
            </div>
            <DialogFooter>
              <DialogClose asChild>
                <Button variant="outline">{__('Cancel', 'growfund')}</Button>
              </DialogClose>

              <Button type="submit">{__('Done', 'growfund')}</Button>
            </DialogFooter>
          </Form>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export default EmailConfigDialog;
