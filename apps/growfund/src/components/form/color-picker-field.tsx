import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import { type FieldValues } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import ColorPicker from '@/components/ui/color-picker';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface ColorPickerFieldProps<T extends FieldValues> extends ControllerField<T> {
  placeholder?: string;
  description?: string;
  defaultValue?: string;
}
function ColorPickerField<T extends FieldValues>({
  control,
  name,
  label,
  placeholder = __('Pick a color', 'growfund'),
  description,
  disabled = false,
  className,
  defaultValue,
}: ColorPickerFieldProps<T>) {
  const [open, setOpen] = useState(false);
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        const getDisplayText = () => {
          if (!field.value) {
            return <span>{placeholder}</span>;
          }
          return <span className="growfund-text-fg-secondary growfund-typo-small">{field.value}</span>;
        };

        return (
          <FormItem className="growfund-w-full growfund-space-y-2 growfund-select-none">
            {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}

            <FormControl>
              <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      'growfund-w-full growfund-justify-start growfund-text-left growfund-font-normal growfund-px-3',
                      !field.value && 'growfund-text-muted-foreground',
                      fieldState.error && 'growfund-border-border-critical',
                      disabled && 'growfund-opacity-50',
                      className,
                    )}
                  >
                    <div
                      className="growfund-w-4 growfund-h-4 growfund-rounded-full growfund-border"
                      style={{
                        backgroundColor: typeof field.value === 'string' ? field.value : '#FFFFFF',
                      }}
                    />
                    {getDisplayText()}
                  </Button>
                </PopoverTrigger>

                <PopoverContent className="w-auto p-4" align="start">
                  <div className="growfund-flex growfund-items-center growfund-gap-2">
                    <ColorPicker
                      defaultValue={defaultValue}
                      color={field.value}
                      onChange={field.onChange}
                      closePopover={() => {
                        setOpen(false);
                      }}
                    />
                  </div>
                </PopoverContent>
              </Popover>
            </FormControl>

            {isDefined(description) && <FormDescription>{description}</FormDescription>}
            <FormMessage />
          </FormItem>
        );
      }}
    />
  );
}

export { ColorPickerField };
