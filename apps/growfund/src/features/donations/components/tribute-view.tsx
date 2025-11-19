import { __ } from '@wordpress/i18n';
import { Flower2, Plus } from 'lucide-react';
import { useState } from 'react';
import { type FieldErrors, useFormContext, useWatch } from 'react-hook-form';

import { Alert, AlertTitle } from '@/components/ui/alert';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import TributeOptionsDialogForm from '@/features/donations/components/dialogs/tribute-options-dialog-form';
import TributeCard from '@/features/donations/components/tribute-options-card';
import { type TributeFields } from '@/features/donations/schemas/donation';
import { type DonationForm } from '@/features/donations/schemas/donation-form';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface TributeViewProps {
  campaign?: Campaign;
}

const TributeView = ({ campaign }: TributeViewProps) => {
  const form = useFormContext<DonationForm>();
  const [dialogOpen, setDialogOpen] = useState(false);

  const handleRemove = () => {
    form.setValue('tribute_type', undefined);
    form.setValue('tribute_notification_type', undefined);
    form.setValue('tribute_salutation', '');
    form.setValue('tribute_to', '');
    form.setValue('tribute_notification_recipient_name', '');
    form.setValue('tribute_notification_recipient_phone', '');
    form.setValue('tribute_notification_recipient_email', undefined);
    form.setValue('tribute_notification_recipient_address', null);
  };

  const handleEdit = () => {
    setDialogOpen(true);
  };
  const handleSave = (data: TributeFields) => {
    for (const [key, value] of Object.entries(data)) {
      form.setValue(key as keyof TributeFields, value);
    }
  };

  const tributeFields = useWatch({
    control: form.control,
    name: [
      'tribute_type',
      'tribute_salutation',
      'tribute_to',
      'tribute_notification_type',
      'tribute_notification_recipient_name',
      'tribute_notification_recipient_phone',
      'tribute_notification_recipient_email',
      'tribute_notification_recipient_address',
    ],
  });

  const hasContent = tributeFields.some((field) =>
    typeof field === 'string' ? field.trim().length > 0 : field != null,
  );

  const errors: FieldErrors<TributeFields> = {
    tribute_type: form.formState.errors.tribute_type,
    tribute_salutation: form.formState.errors.tribute_salutation,
    tribute_to: form.formState.errors.tribute_to,
    tribute_notification_type: form.formState.errors.tribute_notification_type,
    tribute_notification_recipient_name: form.formState.errors.tribute_notification_recipient_name,
    tribute_notification_recipient_phone:
      form.formState.errors.tribute_notification_recipient_phone,
    tribute_notification_recipient_email:
      form.formState.errors.tribute_notification_recipient_email,
    tribute_notification_recipient_address:
      form.formState.errors.tribute_notification_recipient_address,
  };

  const hasError = !!Object.keys(errors).find((key) =>
    isDefined(errors[key as keyof TributeFields]),
  );

  if (!isDefined(campaign)) {
    return null;
  }

  return (
    <Box>
      <BoxContent
        className={cn(
          hasError && 'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
        )}
      >
        <div className="growfund-space-y-4 growfund-flex growfund-flex-col">
          <TributeOptionsDialogForm
            campaign={campaign}
            open={dialogOpen}
            onOpenChange={setDialogOpen}
            onSave={handleSave}
            errors={errors}
          />

          {hasContent ? (
            <TributeCard onEdit={handleEdit} onRemove={handleRemove} />
          ) : (
            <>
              <div className="growfund-flex growfund-items-center growfund-gap-2">
                <Flower2 className="growfund-size-5 growfund-text-icon-primary" />
                <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
                  {__('Tribute', 'growfund')}
                </h6>
              </div>
              {hasError && campaign.tribute_requirement === 'required' && (
                <Alert variant="destructive">
                  <AlertTitle className="growfund-typo-small !growfund-font-normal">
                    {__('A tribute is required for this donation.', 'growfund')}
                  </AlertTitle>
                </Alert>
              )}
              <Button
                variant="secondary"
                onClick={() => {
                  setDialogOpen(true);
                }}
                className="growfund-gap-2"
              >
                <Plus className="growfund-size-4" />
                {__('Add Tribute', 'growfund')}
              </Button>
            </>
          )}
        </div>
      </BoxContent>
    </Box>
  );
};

export default TributeView;
