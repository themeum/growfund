import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';

import { MediaField } from '@/components/form/media-field';
import { TextField } from '@/components/form/text-field';
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
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import {
    RewardItemFormSchema,
    type RewardItem,
    type RewardItemForm,
} from '@/features/campaigns/schemas/reward-item';
import {
    useCreateRewardItemMutation,
    useUpdateRewardItemMutation,
} from '@/features/campaigns/services/reward-item';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

const ManageRewardItemDialog = ({
  defaultValues,
  children,
}: {
  defaultValues?: RewardItem;
  children: React.ReactNode;
}) => {
  const [open, setOpen] = useState(false);
  const form = useForm<RewardItemForm>({
    resolver: zodResolver(RewardItemFormSchema),
  });

  useEffect(() => {
    if (!isDefined(defaultValues)) return;
    form.reset.call(null, {
      title: defaultValues.title,
      image: defaultValues.image ?? undefined,
    });
  }, [defaultValues, form.reset]);

  const { campaignId } = useCampaignBuilderContext();

  const createRewardItemMutation = useCreateRewardItemMutation();
  const updateRewardItemMutation = useUpdateRewardItemMutation();

  const { applyMiddleware } = useDialogCloseMiddleware();
  const { createErrorHandler } = useFormErrorHandler(form);

  const isEditMode = isDefined(defaultValues);

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(setOpen)}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <Form {...form}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>{__('Add Item', 'growfund')}</DialogTitle>
            <DialogCloseButton className="growfund-size-6" />
          </DialogHeader>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                if (!campaignId) {
                  return;
                }

                if (isEditMode) {
                  updateRewardItemMutation.mutate(
                    { ...values, campaign_id: campaignId, id: defaultValues.id },
                    {
                      onError: createErrorHandler(),
                      onSuccess: () => {
                        setOpen(false);
                        form.reset();
                      },
                    },
                  );
                  return;
                }
                createRewardItemMutation.mutate(
                  { ...values, campaign_id: campaignId },
                  {
                    onError: createErrorHandler(),
                    onSuccess: () => {
                      setOpen(false);
                      form.reset();
                    },
                  },
                );
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            <div className="growfund-p-4 growfund-pt-0 growfund-space-y-4">
              <TextField
                control={form.control}
                name="title"
                label={__('Title', 'growfund')}
                placeholder={__('Enter title', 'growfund')}
                autoFocus
              />
              <MediaField
                control={form.control}
                name="image"
                label={__('Image', 'growfund')}
                accept={[MediaType.IMAGES]}
              />
            </div>
            <DialogFooter>
              <Button
                variant="outline"
                onClick={() => {
                  setOpen(false);
                }}
              >
                {__('Cancel', 'growfund')}
              </Button>

              <Button
                type="submit"
                disabled={createRewardItemMutation.isPending || updateRewardItemMutation.isPending}
                loading={createRewardItemMutation.isPending || updateRewardItemMutation.isPending}
              >
                {__('Save', 'growfund')}
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Form>
    </Dialog>
  );
};

export default ManageRewardItemDialog;
