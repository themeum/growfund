import { zodResolver } from '@hookform/resolvers/zod';
import { DashIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { Gift } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm, useWatch } from 'react-hook-form';

import { CheckboxField } from '@/components/form/checkbox-field';
import { DatePickerField } from '@/components/form/date-picker-field';
import { EditorField } from '@/components/form/editor-field';
import { MediaField } from '@/components/form/media-field';
import { RadioField } from '@/components/form/radio-field';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Form, FormLabel } from '@/components/ui/form';
import { ScrollArea } from '@/components/ui/scroll-area';
import RewardItemsSelection from '@/features/campaigns/components/dialogs/manage-reward-dialog/reward-items-selection';
import { useCampaignBuilderContext } from '@/features/campaigns/contexts/campaign-builder';
import { type RewardForm, RewardSchema } from '@/features/campaigns/schemas/reward';
import {
  useCreateRewardMutation,
  useUpdateRewardMutation,
} from '@/features/campaigns/services/reward';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

import InformationPanel from './information-panel';
import ShippingCosts from './shipping-costs';

interface ManageRewardDialogProps {
  children?: React.ReactNode;
  defaultValues?: RewardForm & { id: string };
  rewardLeft?: number | null;
  numberOfContributors?: number | null;
}

const ManageRewardDialog = ({
  defaultValues,
  rewardLeft,
  numberOfContributors,
  children,
}: ManageRewardDialogProps) => {
  const [open, setOpen] = useState(false);
  const form = useForm<RewardForm>({
    resolver: zodResolver(RewardSchema),
    defaultValues: {
      quantity_type: 'unlimited',
      time_limit_type: 'no-limit',
      reward_type: 'physical-goods',
      items: [],
    },
  });

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [defaultValues, form.reset]);

  const { campaignId } = useCampaignBuilderContext();

  const allowLocalPickup = useWatch({ control: form.control, name: 'allow_local_pickup' });
  const rewardQuantityType = useWatch({ control: form.control, name: 'quantity_type' });
  const rewardType = useWatch({ control: form.control, name: 'reward_type' });
  const timeLimitType = useWatch({ control: form.control, name: 'time_limit_type' });

  const createRewardMutation = useCreateRewardMutation();
  const updateRewardMutation = useUpdateRewardMutation();

  const { applyMiddleware } = useDialogCloseMiddleware();
  const { createErrorHandler } = useFormErrorHandler(form);

  const isEditMode = isDefined(defaultValues);

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(setOpen)}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[57.5rem] growfund-gap-0" tabIndex={undefined}>
        <Form {...form}>
          <form
            onSubmit={form.handleSubmit(
              (values) => {
                if (!campaignId) {
                  return;
                }

                if (isEditMode && !defaultValues.id) {
                  return;
                }

                if (isEditMode) {
                  updateRewardMutation.mutate(
                    {
                      ...values,
                      campaign_id: campaignId,
                      reward_id: defaultValues.id,
                    },
                    {
                      onError: createErrorHandler(),
                      onSuccess() {
                        form.reset();
                        setOpen(false);
                      },
                    },
                  );
                  return;
                }

                createRewardMutation.mutate(
                  { ...values, campaign_id: campaignId },
                  {
                    onError: createErrorHandler(),
                    onSuccess() {
                      form.reset();
                      setOpen(false);
                    },
                  },
                );
              },
              (errors) => {
                console.error(errors);
              },
            )}
          >
            <DialogHeader className="growfund-flex-row growfund-items-center growfund-justify-between growfund-space-y-0 growfund-max-h-[3.75rem]">
              <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
                <Gift className="growfund-text-icon-primary" />
                {__('Add a Reward', 'growfund')}
              </DialogTitle>
              <div className="growfund-flex growfund-items-center growfund-gap-3">
                <Button
                  variant="outline"
                  type="button"
                  onClick={() => {
                    setOpen(false);
                  }}
                >
                  {__('Cancel', 'growfund')}
                </Button>
                <Button type="submit">{__('Save', 'growfund')}</Button>
              </div>
            </DialogHeader>
            <div className="growfund-px-12 growfund-py-7">
              <div className="growfund-grid growfund-grid-cols-[auto_20rem] growfund-gap-5 growfund-h-[calc(100svh-11.25rem)]">
                <ScrollArea className="growfund-flex growfund-flex-1">
                  <div className="growfund-grid growfund-gap-4 growfund-w-full">
                    <Box className="growfund-p-4 growfund-space-y-4">
                      <TextField
                        control={form.control}
                        name="title"
                        label={__('Title', 'growfund')}
                        placeholder={__('e.g. Limited Edition', 'growfund')}
                        autoFocus
                      />

                      <TextField
                        control={form.control}
                        name="amount"
                        label={__('Pledge Amount', 'growfund')}
                        type="number"
                        placeholder={__('e.g. 50.00', 'growfund')}
                      />

                      <TextareaField
                        control={form.control}
                        name="description"
                        label={__('Description', 'growfund')}
                        placeholder={__(
                          'e.g., For instance, grab a sneak peek - Fresh from the printer!',
                          'growfund',
                        )}
                      />
                      <MediaField
                        control={form.control}
                        name="image"
                        label={__('Reward Image', 'growfund')}
                        uploadButtonLabel={__('Upload Image', 'growfund')}
                        dropzoneLabel={__('Drop image here to upload', 'growfund')}
                        accept={[MediaType.IMAGES]}
                      />
                    </Box>

                    <RewardItemsSelection />

                    <Box className="growfund-p-4 growfund-space-y-4">
                      <RadioField
                        control={form.control}
                        name="quantity_type"
                        label={__('Available Reward Quantity', 'growfund')}
                        options={[
                          { label: __('Unlimited', 'growfund'), value: 'unlimited' },
                          { label: __('Limited', 'growfund'), value: 'limited' },
                        ]}
                      />

                      {rewardQuantityType === 'limited' && (
                        <TextField
                          control={form.control}
                          name="quantity_limit"
                          type="number"
                          placeholder={__('e.g. 50', 'growfund')}
                        />
                      )}

                      <RadioField
                        control={form.control}
                        name="time_limit_type"
                        label={__('Time Limit', 'growfund')}
                        options={[
                          { label: __('No Limit', 'growfund'), value: 'no-limit' },
                          {
                            label: __('Specify Start & End', 'growfund'),
                            value: 'specific-date',
                          },
                        ]}
                      />

                      {timeLimitType === 'specific-date' && (
                        <div className="growfund-flex growfund-items-start growfund-gap-2">
                          <DatePickerField
                            control={form.control}
                            name="limit_start_date"
                            placeholder={__('Pick a Date', 'growfund')}
                          />
                          <DashIcon className="growfund-size-4 growfund-flex-shrink-0 growfund-mt-3" />
                          <DatePickerField
                            control={form.control}
                            name="limit_end_date"
                            placeholder={__('Pick a Date', 'growfund')}
                          />
                        </div>
                      )}

                      <DatePickerField
                        control={form.control}
                        name="estimated_delivery_date"
                        label={__('Estimated Delivery', 'growfund')}
                        placeholder={__('Pick a Date', 'growfund')}
                        clearable
                      />
                    </Box>
                    {rewardType !== 'digital-goods' && (
                      <Box className="growfund-p-4 growfund-space-y-4">
                        <FormLabel>{__('Shipping', 'growfund')}</FormLabel>
                        <ShippingCosts />

                        <CheckboxField
                          control={form.control}
                          name="allow_local_pickup"
                          label={__('Local pickup or event available', 'growfund')}
                        />

                        {allowLocalPickup && (
                          <EditorField
                            control={form.control}
                            name="local_pickup_instructions"
                            label={__('Local Pickup Instructions', 'growfund')}
                            placeholder={__(
                              'Specific locations or instructions for local pickup',
                              'growfund',
                            )}
                            className="growfund-modal-richtext-container"
                          />
                        )}
                      </Box>
                    )}
                  </div>
                </ScrollArea>
                <InformationPanel
                  rewardLeft={rewardLeft}
                  numberOfContributors={numberOfContributors}
                />
              </div>
            </div>
          </form>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default ManageRewardDialog;
