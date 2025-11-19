import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { FileEdit } from 'lucide-react';
import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';

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
import { type Tag, type TagForm, TagFormSchema } from '@/features/tags/schemas/tag';
import { useCreateTagMutation, useUpdateTagMutation } from '@/features/tags/services/tag';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { isDefined } from '@/utils';

interface TagFormDialogProps {
  defaultValues?: Tag;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const TagFormDialog = ({
  children,
  open,
  onOpenChange,
  defaultValues,
}: React.PropsWithChildren<TagFormDialogProps>) => {
  const form = useForm<TagForm>({
    resolver: zodResolver(TagFormSchema),
  });

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [form.reset, defaultValues]);

  const { createErrorHandler } = useFormErrorHandler(form);
  const createTagMutation = useCreateTagMutation();
  const updateTagMutation = useUpdateTagMutation();

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[33.75rem] growfund-w-full">
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                if (isDefined(defaultValues)) {
                  updateTagMutation.mutate(
                    { ...values, id: defaultValues.id },
                    {
                      onError: createErrorHandler(),
                      onSuccess() {
                        form.reset();
                        onOpenChange(false);
                      },
                    },
                  );
                  return;
                }
                createTagMutation.mutate(values, {
                  onError: createErrorHandler(),
                  onSuccess: () => {
                    form.reset();
                    onOpenChange(false);
                  },
                });
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            <DialogHeader>
              <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
                <FileEdit className="growfund-size-6 growfund-text-icon-primary" />
                {isDefined(defaultValues) ? __('Edit Tag', 'growfund') : __('New Tag', 'growfund')}
              </DialogTitle>
              <DialogCloseButton />
            </DialogHeader>
            <div className="growfund-space-y-4 growfund-p-4">
              <TextField
                label={__('Name', 'growfund')}
                control={form.control}
                name="name"
                placeholder={__('e.g., fundraising', 'growfund')}
                autoFocusVisible
              />
              <TextField
                label={__('Slug', 'growfund')}
                control={form.control}
                name="slug"
                placeholder={__('e.g., fund-raising', 'growfund')}
              />
              <TextareaField
                label={__('Description', 'growfund')}
                control={form.control}
                name="description"
                placeholder={__(
                  'e.g., Dedicated to providing immediate support and essential resources to communities affected by unexpected crises.',
                  'growfund',
                )}
              />
            </div>
            <DialogFooter>
              <Button
                variant="outline"
                onClick={() => {
                  onOpenChange(false);
                }}
              >
                {__('Cancel', 'growfund')}
              </Button>
              <Button
                type="submit"
                loading={createTagMutation.isPending || updateTagMutation.isPending}
                disabled={createTagMutation.isPending || updateTagMutation.isPending}
              >
                {__('Save', 'growfund')}
              </Button>
            </DialogFooter>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export { TagFormDialog };
