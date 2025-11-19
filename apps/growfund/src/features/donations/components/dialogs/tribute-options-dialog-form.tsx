import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { Flower2 } from 'lucide-react';
import React, { useCallback, useEffect, useMemo } from 'react';
import { type FieldErrors, useForm, useWatch } from 'react-hook-form';

import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import { Container } from '@/components/layouts/container';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Form } from '@/components/ui/form';
import { ScrollArea } from '@/components/ui/scroll-area';
import { UserFormAddress } from '@/components/user-form/user-form';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { type TributeFields, TributeFieldSchema } from '@/features/donations/schemas/donation';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';

interface TributeOptionsDialogProps extends React.PropsWithChildren {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  isEditMode?: boolean;
  onSave: (data: TributeFields) => void;
  campaign: Campaign;
  errors: FieldErrors<TributeFields>;
}

const TributeOptionsDialogForm = ({
  open,
  onOpenChange,
  isEditMode = false,
  onSave,
  campaign,
  errors,
}: TributeOptionsDialogProps) => {
  const getDefaultTributeNotificationType = useCallback(() => {
    if (campaign.tribute_notification_preference === 'donor-decide') {
      return;
    }

    return campaign.tribute_notification_preference;
  }, [campaign.tribute_notification_preference]);

  const form = useForm<TributeFields>({
    resolver: zodResolver(TributeFieldSchema),
    defaultValues: {
      ...getDefaults(TributeFieldSchema._def.schema),
      tribute_notification_type: getDefaultTributeNotificationType(),
    },
  });

  const notificationType = useWatch({
    control: form.control,
    name: 'tribute_notification_type',
  });

  useEffect(() => {
    Object.entries(errors).forEach(([key, value]) => {
      if (isDefined(value)) {
        form.setError(key as keyof TributeFields, {
          type: value.type as string,
          message: value.message,
        });
      }
    });
  }, [errors, form]);

  const shouldShowAddress =
    notificationType === 'send-post-mail' || notificationType === 'send-ecard-and-post-mail';

  const tributeOptions = useMemo(() => {
    return (
      campaign.tribute_options?.map((option) => {
        return {
          value: option.message,
          label: option.message,
        };
      }) ?? []
    );
  }, [campaign.tribute_options]);

  const onSubmit = (data: TributeFields) => {
    onSave(data);
    onOpenChange(false);
  };

  const { applyMiddleware } = useDialogCloseMiddleware();

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(onOpenChange)}>
      <DialogContent className="growfund-max-w-[52rem]" tabIndex={undefined}>
        <DialogHeader className="growfund-max-h-[3.75rem]">
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <Flower2 className="growfund-size-5 growfund-text-icon-primary" />
            {isEditMode ? __('Edit Tribute', 'growfund') : __('Add Tribute', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>
        <ScrollArea>
          <Container size="xs" className="growfund-mt-3 growfund-max-h-[calc(100svh-280px)]">
            <Form {...form}>
              <div className="growfund-space-y-4">
                <Card>
                  <CardHeader className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-border-b growfund-border-gray-200">
                    {__('Tribute Option', 'growfund')}
                  </CardHeader>
                  <CardContent className="growfund-p-6 growfund-space-y-4">
                    <SelectField
                      control={form.control}
                      name="tribute_type"
                      label={__('Tribute Option', 'growfund')}
                      options={tributeOptions}
                    />
                    <TextField
                      control={form.control}
                      name="tribute_salutation"
                      label={__('Tribute Prepended Label', 'growfund')}
                      placeholder={__('Honoree', 'growfund')}
                    />
                    <TextField
                      control={form.control}
                      name="tribute_to"
                      label={__('Tribute to', 'growfund')}
                      placeholder={__('Jhon Doe', 'growfund')}
                    />
                  </CardContent>
                </Card>
                <Card>
                  <CardHeader className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-border-b growfund-border-gray-200">
                    {__('Donation Notification', 'growfund')}
                  </CardHeader>
                  <CardContent className="growfund-p-6 growfund-space-y-4">
                    {campaign.tribute_notification_preference === 'donor-decide' && (
                      <SelectField
                        control={form.control}
                        name="tribute_notification_type"
                        label={__('Donation Notification', 'growfund')}
                        options={[
                          {
                            label: __('Send eCard', 'growfund'),
                            value: 'send-ecard',
                          },
                          {
                            label: __('Send Post Mail', 'growfund'),
                            value: 'send-post-mail',
                          },
                          {
                            label: __('Send eCard and Post Mail', 'growfund'),
                            value: 'send-ecard-and-post-mail',
                          },
                        ]}
                      />
                    )}
                    <div className="growfund-text-fg-primary growfund-font-medium growfund-typo-small">
                      {__('Send Notification to', 'growfund')}
                    </div>
                    <Card>
                      <CardContent className="growfund-p-6 growfund-space-y-4">
                        <TextField
                          control={form.control}
                          name="tribute_notification_recipient_name"
                          label={__('Recipient Name', 'growfund')}
                          placeholder={__('Enter recipient name', 'growfund')}
                        />
                        <TextField
                          control={form.control}
                          name="tribute_notification_recipient_email"
                          label={__('Email', 'growfund')}
                          placeholder={__('Enter email', 'growfund')}
                        />
                        <TextField
                          control={form.control}
                          name="tribute_notification_recipient_phone"
                          label={__('Phone Number', 'growfund')}
                          placeholder={__('Enter phone', 'growfund')}
                        />
                        {shouldShowAddress && (
                          <UserFormAddress keyPrefix="tribute_notification_recipient_address" />
                        )}
                      </CardContent>
                    </Card>
                  </CardContent>
                </Card>
              </div>
            </Form>
          </Container>
        </ScrollArea>
        <DialogFooter className="growfund-z-highest">
          <Button
            variant="outline"
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Cancel', 'growfund')}
          </Button>
          <Button variant="primary" onClick={form.handleSubmit(onSubmit)}>
            {__('Save', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

TributeOptionsDialogForm.displayName = 'TributeOptionsDialogForm';

export default TributeOptionsDialogForm;
