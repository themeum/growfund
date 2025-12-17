import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { MediaField } from '@/components/form/media-field';
import { TextField } from '@/components/form/text-field';
import CampaignRewardFallback from '@/components/pro-fallbacks/campaign/campaign-reward-fallback';
import { Button } from '@/components/ui/button';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import {
  type RewardItem,
  type RewardItemForm,
  RewardItemFormSchema,
} from '@/features/campaigns/schemas/reward-item';
import {
  useCreateRewardItemMutation,
  useUpdateRewardItemMutation,
} from '@/features/campaigns/services/reward-item';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { getDefaults } from '@/lib/zod';
import { MediaType } from '@/utils/media';

interface CreateRewardItemProps {
  onSave: (value: { id: string; quantity: number }) => void;
  onCancel: () => void;
  mode?: 'create' | 'edit';
  defaultValues?: RewardItem;
}

const CreateRewardItem = ({
  onSave,
  onCancel,
  mode = 'create',
  defaultValues,
}: CreateRewardItemProps) => {
  const form = useForm<RewardItemForm>({
    resolver: zodResolver(RewardItemFormSchema),
    defaultValues:
      mode === 'edit' && defaultValues
        ? { ...defaultValues, image: defaultValues.image }
        : getDefaults(RewardItemFormSchema),
  });

  const { campaignId } = useCampaignBuilderContext();

  const createRewardItemMutation = useCreateRewardItemMutation();
  const updateRewardItemMutation = useUpdateRewardItemMutation();

  const { createErrorHandler } = useFormErrorHandler(form);

  return (
    <div className="growfund-space-y-3">
      <TextField
        control={form.control}
        name="title"
        label={__('Title', 'growfund')}
        placeholder={__('Enter title', 'growfund')}
      />
      <MediaField
        control={form.control}
        name="image"
        label={__('Image', 'growfund')}
        dropzoneLabel={__('Drop image here or click to upload', 'growfund')}
        accept={[MediaType.IMAGES]}
      />

      <TextField
        control={form.control}
        name="quantity"
        type="number"
        label={__('Quantity', 'growfund')}
        placeholder={__('Enter quantity', 'growfund')}
      />

      <div className="growfund-flex growfund-justify-end growfund-gap-4 growfund-pt-2">
        <Button variant="ghost" onClick={onCancel}>
          {__('Cancel', 'growfund')}
        </Button>
        <ElementWrapper
          fallback={
            <CampaignRewardFallback
              title={__('Unlock Unlimited Items', 'growfund')}
              description={__(
                "Maximize your campaign's appeal with more items in rewards. Upgrade to Pro for unlimited items attract more backers.",
                'growfund',
              )}
            >
              <Button variant="primary">{__('Save', 'growfund')}</Button>
            </CampaignRewardFallback>
          }
        >
          <Button
            variant="primary"
            disabled={!form.formState.isDirty}
            onClick={form.handleSubmit(
              (values) => {
                if (!campaignId) {
                  return;
                }
                if (mode === 'create') {
                  createRewardItemMutation.mutate(
                    { ...values, campaign_id: campaignId },
                    {
                      onSuccess: (data) => {
                        onSave({ id: data.id, quantity: values.quantity ?? 1 });
                      },
                      onError: createErrorHandler(),
                    },
                  );
                  return;
                }

                updateRewardItemMutation.mutate(
                  { ...values, campaign_id: campaignId, id: defaultValues?.id ?? '' },
                  {
                    onSuccess: () => {
                      onSave({ id: defaultValues?.id ?? '', quantity: values.quantity ?? 1 });
                    },
                    onError: createErrorHandler(),
                  },
                );
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            {__('Save', 'growfund')}
          </Button>
        </ElementWrapper>
      </div>
    </div>
  );
};

export default CreateRewardItem;
