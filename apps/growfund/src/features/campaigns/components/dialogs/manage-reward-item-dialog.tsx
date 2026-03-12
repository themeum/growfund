import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { File, ShoppingBag } from 'lucide-react';
import { useEffect } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import FileUploaderField from '@/components/form/file-uploader-field';
import { MediaField } from '@/components/form/media-field';
import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
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
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import {
  RewardItemBaseSchema,
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
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

const ManageRewardItemDialog = ({
  defaultValues,
  open,
  onOpenChange,
  rewardItemType,
}: {
  defaultValues?: RewardItem;
  rewardItemType?: 'digital' | 'physical';
  open?: boolean;
  onOpenChange: (open: boolean) => void;
}) => {
  const form = useForm<RewardItemForm>({
    resolver: zodResolver(RewardItemFormSchema),
    defaultValues: isDefined(defaultValues) ? defaultValues : getDefaults(RewardItemBaseSchema),
  });

  const { campaignId } = useCampaignBuilderContext();

  const createRewardItemMutation = useCreateRewardItemMutation();
  const updateRewardItemMutation = useUpdateRewardItemMutation();

  const { applyMiddleware } = useDialogCloseMiddleware();
  const { createErrorHandler } = useFormErrorHandler(form);

  const isEditMode = isDefined(defaultValues);
  const assetType = useWatch({ control: form.control, name: 'asset_type' });

  useEffect(() => {
    if (!open) return;

    if (isDefined(defaultValues)) {
      form.reset({
        ...defaultValues,
      });

      return;
    }

    form.reset({
      ...getDefaults(RewardItemBaseSchema),
      type: rewardItemType,
    });

    if (rewardItemType === 'digital') {
      form.setValue('asset_type', 'file');
    }

    if (rewardItemType === 'physical') {
      form.setValue('asset_type', undefined);
      form.setValue('asset', undefined);
      form.setValue('asset_url', undefined);
    }
  }, [open, defaultValues, rewardItemType, form]);

  useEffect(() => {
    if (assetType === 'url') {
      form.setValue('asset', undefined);
    }

    if (assetType === 'file') {
      form.setValue('asset_url', undefined);
    }
  }, [assetType, form]);

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(onOpenChange)}>
      <Form {...form}>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>
              <div className="growfund-flex growfund-gap-3 growfund-items-center">
                {rewardItemType === 'digital' ? (
                  <File className="growfund-size-5" />
                ) : (
                  <ShoppingBag className="growfund-size-5" />
                )}
                <span>{isEditMode ? __('Edit Item', 'growfund') : __('Add Item', 'growfund')}</span>
              </div>
            </DialogTitle>
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
                    {
                      ...values,
                      campaign_id: campaignId,
                      id: defaultValues.id,
                    },
                    {
                      onError: createErrorHandler(),
                      onSuccess: () => {
                        onOpenChange(false);
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
                      onOpenChange(false);
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
              {rewardItemType === 'digital' && (
                <>
                  <SelectField
                    control={form.control}
                    name="asset_type"
                    label={__('Asset Type', 'growfund')}
                    options={[
                      { label: __('URL', 'growfund'), value: 'url' },
                      { label: __('File', 'growfund'), value: 'file' },
                    ]}
                  />

                  {assetType === 'file' && (
                    <FileUploaderField
                      control={form.control}
                      name="asset"
                      label={__('File', 'growfund')}
                      uploadButtonLabel={__('Upload file', 'growfund')}
                    />
                  )}

                  {assetType === 'url' && (
                    <TextField
                      control={form.control}
                      name="asset_url"
                      label={__('URL', 'growfund')}
                      placeholder="https://example.com/file.zip"
                    />
                  )}
                </>
              )}
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
