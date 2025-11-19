import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const MessageFormSchema = z
  .object({
    from: z.number({
      message: __('Pledge From is required', 'growfund'),
    }),
    to: z.number({
      message: __('Pledge To is required', 'growfund'),
    }),
    message: z.string({
      message: __('Appreciation Message is required', 'growfund'),
    }),
  })
  .superRefine(({ from, to }, ctx) => {
    if (from > to) {
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('Min value must be lesser than maximum', 'growfund'),
        path: ['from'],
      });
      ctx.addIssue({
        code: z.ZodIssueCode.custom,
        message: __('Max value must be greater than minimum', 'growfund'),
        path: ['to'],
      });
    }
  });
export type MessageForm = z.infer<typeof MessageFormSchema>;

interface MessageFormProps {
  onRemove?: () => void;
  onSave: (data: MessageForm) => void;
  onCancel: () => void;
  defaultData?: MessageForm;
}

export const MessageForm = ({ defaultData, onRemove, onSave, onCancel }: MessageFormProps) => {
  const form = useForm<MessageForm>({
    resolver: zodResolver(MessageFormSchema),
    defaultValues: defaultData,
  });

  const isEdit = isDefined(defaultData);

  const onSubmit = (values: MessageForm) => {
    onSave(values);
  };

  return (
    <Form {...form}>
      <div className="growfund-flex growfund-flex-col growfund-gap-3">
        <div className="growfund-flex growfund-gap-2 growfund-items-center growfund-justify-center">
          <TextField
            className="growfund-mt-4"
            control={form.control}
            name="from"
            label={__('Pledge Range', 'growfund')}
            placeholder={__('e.g. 5.00', 'growfund')}
            type="number"
            description={__('Min Value', 'growfund')}
          />
          <span className="growfund-text-fg-secondary growfund-items-center">{__('-', 'growfund')}</span>
          <TextField
            className="growfund-mt-8"
            control={form.control}
            name="to"
            placeholder={__('e.g. 50.00', 'growfund')}
            type="number"
            description={__('Max Value', 'growfund')}
          />
        </div>
        <TextareaField
          control={form.control}
          name="message"
          label={__('Appreciation Message', 'growfund')}
          placeholder={__('Enter your appreciation message...', 'growfund')}
        />
        <div
          className={cn(
            'growfund-flex growfund-w-full growfund-gap-2 growfund-mt-1 growfund-justify-end',
            isEdit && 'growfund-justify-between',
          )}
        >
          {isEdit && (
            <Button variant="secondary" onClick={onRemove} className="growfund-text-fg-critical">
              {__('Delete', 'growfund')}
            </Button>
          )}

          <div className="growfund-flex growfund-gap-2">
            <Button variant="outline" onClick={onCancel}>
              {__('Cancel', 'growfund')}
            </Button>
            <Button
              onClick={form.handleSubmit(onSubmit, (errors) => {
                console.error(errors);
              })}
            >
              {__('Save', 'growfund')}
            </Button>
          </div>
        </div>
      </div>
    </Form>
  );
};
