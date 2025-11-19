import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { MediaField } from '@/components/form/media-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Form } from '@/components/ui/form';
import {
    ManualPaymentFormSchema,
    type ManualPaymentForm,
} from '@/features/settings/features/payments/schemas/payment';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';

interface ManualPaymentFormDialogProps {
  defaultValues?: ManualPaymentForm;
  open: boolean;
  onOpenChange: (open: boolean) => void;
  onSave: (payments: ManualPaymentForm) => void;
}

const ManualPaymentFormDialog = ({
  open,
  onOpenChange,
  defaultValues,
  onSave,
}: ManualPaymentFormDialogProps) => {
  const form = useForm<ManualPaymentForm>({
    resolver: zodResolver(ManualPaymentFormSchema),
    defaultValues: getDefaults(ManualPaymentFormSchema),
  });

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [defaultValues, form.reset]);

  const { applyMiddleware } = useDialogCloseMiddleware();

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(onOpenChange)}>
      <DialogContent
        className="growfund-max-w-[33.75rem] growfund-w-full growfund-bg-background-white"
        tabIndex={undefined}
      >
        <DialogHeader>
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            {__('New Manual Payment', 'growfund')}
          </DialogTitle>
          <DialogCloseButton
            onClick={() => {
              onOpenChange(false);
            }}
          />
        </DialogHeader>
        <form
          onSubmit={form.handleSubmit(
            (values) => {
              onSave(values);
              onOpenChange(false);
              form.reset();
            },
            (errors) => {
              console.error(errors);
            },
          )}
        >
          <Form {...form}>
            <div className="growfund-space-y-4 growfund-px-4 growfund-pb-4">
              <TextField
                label={__('Title', 'growfund')}
                control={form.control}
                name="title"
                placeholder={__('e.g., Offline Donation', 'growfund')}
                autoFocusVisible
              />

              <MediaField control={form.control} name="logo" label={__('Icon', 'growfund')} />

              <TextareaField
                label={__('Instruction', 'growfund')}
                control={form.control}
                name="instruction"
                rows={6}
                placeholder={__(
                  `e.g., To make an offline donation toward this cause, follow these steps: 1. Write a check payable to "{sitename}"`,
                  'growfund',
                )}
              />
            </div>
          </Form>
          <DialogFooter>
            <Button
              variant="outline"
              onClick={() => {
                onOpenChange(false);
              }}
            >
              {__('Cancel', 'growfund')}
            </Button>
            <Button type="submit">{__('Save', 'growfund')}</Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
};

export { ManualPaymentFormDialog };
