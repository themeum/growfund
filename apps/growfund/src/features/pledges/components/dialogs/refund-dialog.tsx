import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

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
    DialogTrigger,
} from '@/components/ui/dialog';
import { Form } from '@/components/ui/form';
import { getDefaults } from '@/lib/zod';
interface RefundDialogProps {
  open: boolean;
  setIsOpen: (state: boolean) => void;
  value?: string | null;
  onChange: (reasons: string) => void;
}
const RefundSchema = z.object({
  reason: z.string({ message: __('Refund Reason is required', 'growfund') }),
  amount: z.number().default(0),
});

type RefundFormFields = z.infer<typeof RefundSchema>;

export const RefundDialog = ({
  children,
  open,
  setIsOpen,
  value,
  onChange,
}: React.PropsWithChildren<RefundDialogProps>) => {
  const form = useForm<RefundFormFields>({
    resolver: zodResolver(RefundSchema),
    defaultValues: getDefaults(RefundSchema),
  });

  return (
    <Form {...form}>
      <Dialog open={open} onOpenChange={setIsOpen}>
        {children}
        <DialogContent className="growfund-max-w-[40rem]">
          <DialogHeader>
            <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
              {__('Refund to baker', 'growfund')}
            </DialogTitle>
            <DialogCloseButton />
          </DialogHeader>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                onChange(values.reason);

                if (form.formState.errors.reason) {
                  return;
                }
                setIsOpen(false);
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            <div className="growfund-flex growfund-flex-col growfund-gap-4 growfund-p-4">
              <TextField
                control={form.control}
                type="number"
                name="amount"
                label={__('Refund Amount($)', 'growfund')}
                placeholder={__('100', 'growfund')}
              />
              <TextareaField
                control={form.control}
                name="reason"
                label={__('Refund Reason', 'growfund')}
                placeholder={__('i.e.Customer Dissatisfaction', 'growfund')}
              />
            </div>

            <DialogFooter>
              <Button
                variant="outline"
                onClick={() => {
                  form.reset({ reason: value ?? '' });
                  setIsOpen(false);
                }}
              >
                {__('Cancel', 'growfund')}
              </Button>
              <Button type="submit">{__('Refund $10', 'growfund')}</Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </Form>
  );
};
export const RefundDialogTrigger = ({ children }: React.PropsWithChildren) => {
  return <DialogTrigger asChild>{children}</DialogTrigger>;
};

RefundDialogTrigger.displayName = 'RefundDialogTrigger';
RefundDialog.displayName = 'RefundDialog';
export default RefundDialog;
