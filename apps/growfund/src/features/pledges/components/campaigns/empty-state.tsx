import { __ } from '@wordpress/i18n';
import { LayoutTemplate } from 'lucide-react';
import { useState } from 'react';
import { useFormContext } from 'react-hook-form';

import { Box } from '@/components/ui/box';
import { FormControl, FormField, FormItem } from '@/components/ui/form';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import SelectCampaignDialog from '@/features/pledges/components/dialogs/campaign-selection-dialog';
import { type PledgeForm } from '@/features/pledges/schemas/pledge-form';
import { cn } from '@/lib/utils';

const AddCampaignEmptyState = ({
  onSelectCampaign,
}: {
  onSelectCampaign: (campaign: Campaign) => void;
}) => {
  const [open, setOpen] = useState(false);

  const form = useFormContext<PledgeForm>();

  return (
    <FormField
      control={form.control}
      name={'campaign_id'}
      render={({ field, fieldState }) => {
        return (
          <FormItem>
            <FormControl>
              <Box
                className={cn(
                  'growfund-grid growfund-place-items-center growfund-gap-4 growfund-p-6',
                  fieldState.error &&
                    'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
                )}
              >
                <LayoutTemplate className="growfund-size-6 growfund-text-icon-primary" />
                <SelectCampaignDialog
                  open={open}
                  onOpenChange={setOpen}
                  onSelect={(campaign) => {
                    field.onChange(campaign.id);
                    onSelectCampaign(campaign);
                    setOpen(false);
                    form.clearErrors();
                  }}
                />
                {fieldState.error && (
                  <p className="growfund-text-[0.8rem] growfund-font-small growfund-text-fg-critical">
                    {__('Please select a campaign.', 'growfund')}
                  </p>
                )}
              </Box>
            </FormControl>
          </FormItem>
        );
      }}
    />
  );
};

export default AddCampaignEmptyState;
