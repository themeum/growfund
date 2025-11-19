import { type FieldValues } from 'react-hook-form';

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Slider } from '@/components/ui/slider';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface RangeSliderFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'min' | 'max' | 'placeholder'> {
  min?: number;
  max?: number;
  step?: number;
}

const RangeSliderField = <T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  min = 0,
  max = 100,
  step = 1,
}: RangeSliderFieldProps<T>) => {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field }) => {
        return (
          <FormItem
            className={cn(
              'growfund-w-full growfund-flex growfund-flex-col growfund-items-start growfund-justify-between growfund-gap-2',
            )}
          >
            <div className="growfund-w-full growfund-space-y-2">
              <div className="growfund-w-full growfund-flex growfund-items-center growfund-justify-between growfund-gap-4">
                {isDefined(label) && <FormLabel className="growfund-shrink-0">{label}</FormLabel>}
                <Input
                  value={isDefined(field.value) ? field.value : ''}
                  onChange={field.onChange}
                  className="growfund-w-full growfund-px-2 growfund-py-0"
                  postfixText="px"
                  rootClassName="growfund-w-[4rem]"
                  placeholder="0"
                />
              </div>
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
            <FormControl>
              <div className="growfund-w-full growfund-gap-4 growfund-items-center">
                <Slider
                  {...field}
                  defaultValue={isDefined(field.value) ? [field.value] : undefined}
                  value={isDefined(field.value) ? [field.value] : undefined}
                  onValueChange={(values) => {
                    field.onChange(values[0]);
                  }}
                  disabled={disabled}
                  className={className}
                  min={min}
                  max={max}
                  step={step}
                />
              </div>
            </FormControl>
          </FormItem>
        );
      }}
    ></FormField>
  );
};

export default RangeSliderField;
