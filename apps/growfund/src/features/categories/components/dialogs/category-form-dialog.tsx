import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { FileEdit } from 'lucide-react';
import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';

import { MediaField } from '@/components/form/media-field';
import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Box } from '@/components/ui/box';
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
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    type Category,
    type CategoryForm,
    CategoryFormSchema,
} from '@/features/categories/schemas/category';
import {
    useCreateCategoryMutation,
    useGetTopLevelCategoriesQuery,
    useUpdateCategoryMutation,
} from '@/features/categories/services/category';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useQueryToOption } from '@/hooks/use-query-to-option';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { isDefined } from '@/utils';

interface CategoryFormDialogProps {
  defaultValues?: Category;
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const CategoryFormDialog = ({
  children,
  open,
  onOpenChange,
  defaultValues,
}: React.PropsWithChildren<CategoryFormDialogProps>) => {
  const form = useForm<CategoryForm>({
    resolver: zodResolver(CategoryFormSchema),
  });
  const { createErrorHandler } = useFormErrorHandler(form);

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [form.reset, defaultValues]);

  const createCategoryMutation = useCreateCategoryMutation();
  const updateCategoryMutation = useUpdateCategoryMutation();
  const topLevelCategoriesQuery = useGetTopLevelCategoriesQuery();

  const parentCategoryOptions = useQueryToOption(
    topLevelCategoriesQuery,
    'id',
    'name',
    defaultValues?.id,
  );

  const { applyMiddleware } = useDialogCloseMiddleware();

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(onOpenChange)}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[33.75rem] growfund-w-full" tabIndex={undefined}>
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                if (isDefined(defaultValues)) {
                  updateCategoryMutation.mutate(
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
                createCategoryMutation.mutate(values, {
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
                {isDefined(defaultValues)
                  ? __('Edit Category', 'growfund')
                  : __('New Category', 'growfund')}
              </DialogTitle>
              <DialogCloseButton />
            </DialogHeader>
            <ScrollArea className="growfund-h-[calc(100svh-240px)] growfund-max-h-[40rem] growfund-p-4">
              <Box className="growfund-space-y-4 growfund-p-4">
                <TextField
                  label={__('Name', 'growfund')}
                  control={form.control}
                  name="name"
                  placeholder={__('e.g., Empowerment', 'growfund')}
                  autoFocusVisible
                />
                <TextField
                  label={__('Slug', 'growfund')}
                  control={form.control}
                  name="slug"
                  placeholder={__('e.g., empowerment', 'growfund')}
                />
                {!defaultValues?.is_default && (
                  <SelectField
                    control={form.control}
                    name="parent_id"
                    options={[
                      { label: __('No Parent', 'growfund'), value: '0' },
                      ...parentCategoryOptions,
                    ]}
                    label={__('Parent', 'growfund')}
                    placeholder={__('Select parent', 'growfund')}
                  />
                )}
                <TextareaField
                  label={__('Description', 'growfund')}
                  control={form.control}
                  name="description"
                  placeholder={__(
                    'e.g., Dedicated to providing immediate support and essential resources to communities affected by unexpected crises.',
                    'growfund',
                  )}
                />

                <MediaField
                  control={form.control}
                  name="image"
                  label={__('Thumbnail', 'growfund')}
                />
              </Box>
            </ScrollArea>

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
                loading={createCategoryMutation.isPending || updateCategoryMutation.isPending}
                disabled={createCategoryMutation.isPending || updateCategoryMutation.isPending}
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

export { CategoryFormDialog };
