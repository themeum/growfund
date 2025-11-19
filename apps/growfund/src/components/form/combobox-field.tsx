import { __ } from '@wordpress/i18n';
import { type FieldValues } from 'react-hook-form';

import Combobox from '@/components/ui/combobox';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { type Option } from '@/types';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface ComboBoxFieldProps<T extends FieldValues> extends Omit<ControllerField<T>, 'readOnly'> {
  options: Option<string>[];
  onAddNewItem?: (value: string) => Promise<boolean>;
  addItemLabel?: string;
  showAddItemButton?: boolean;
}

function ComboBoxField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  options,
  disabled = false,
  className,
  placeholder = __('Select an option', 'growfund'),
  onAddNewItem,
  addItemLabel = __('Add item', 'growfund'),
  showAddItemButton,
}: ComboBoxFieldProps<T>) {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            {isDefined(label) && (
              <div className="growfund-space-y-2">
                {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
                {isDefined(description) && <FormDescription>{description}</FormDescription>}
              </div>
            )}
            <FormControl>
              <Combobox
                className={className}
                onSelect={field.onChange}
                defaultValue={field.value}
                options={options}
                hasError={!!fieldState.error}
                disabled={disabled}
                placeholder={placeholder}
                onAddNewItem={onAddNewItem}
                addItemLabel={addItemLabel}
                showAddItemButton={showAddItemButton}
              />
            </FormControl>
            {isDefined(description) && <FormDescription>{description}</FormDescription>}
            <FormMessage />
          </FormItem>
        );
      }}
    ></FormField>
  );
}

export { ComboBoxField };
