import { __ } from '@wordpress/i18n';
import { type FieldValues } from 'react-hook-form';

import { CampaignFieldProvider } from '@/components/form/campaign-field/campaign-field-context';
import CampaignFieldPopover from '@/components/form/campaign-field/campaign-field-popover';
import { FormField, FormItem, FormLabel } from '@/components/ui/form';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

type CampaignFieldProps<T extends FieldValues> = Omit<ControllerField<T>, 'readOnly' | 'onChange'>;

const CampaignField = <T extends FieldValues>({
  control,
  name,
  label,
  disabled = false,
  placeholder = __('All Campaigns', 'growfund'),
}: CampaignFieldProps<T>) => {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <CampaignFieldProvider field={field} fieldState={fieldState} disabled={disabled}>
            <FormItem className="growfund-space-y-2">
              {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
              <CampaignFieldPopover placeholder={placeholder} />
            </FormItem>
          </CampaignFieldProvider>
        );
      }}
    />
  );
};

export default CampaignField;
