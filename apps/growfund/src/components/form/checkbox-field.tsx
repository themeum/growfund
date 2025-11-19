import { type FieldValues } from 'react-hook-form';

import { Checkbox } from '@/components/ui/checkbox';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface CheckboxFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'readOnly' | 'placeholder' | 'inline'> {
  readOnly?: boolean;
  wrapperClassName?: string;
}

function CheckboxField<T extends FieldValues>({
  control,
  name,
  label,
  disabled = false,
  description,
  className,
  wrapperClassName,
}: CheckboxFieldProps<T>) {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem
            className={cn(
              'growfund-w-full growfund-flex growfund-gap-2',
              isDefined(description) || !!fieldState.error ? 'growfund-items-start' : 'growfund-items-center',
              wrapperClassName,
            )}
          >
            <FormControl>
              <Checkbox
                {...field}
                disabled={disabled}
                className={className}
                checked={!!field.value}
                onCheckedChange={field.onChange}
                aria-readonly
                value={field.value ?? ''}
              />
            </FormControl>
            <div
              className={cn('growfund-flex growfund-flex-col growfund-gap-2', isDefined(description) && 'growfund-pt-0.5')}
            >
              {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
          </FormItem>
        );
      }}
    ></FormField>
  );
}

export { CheckboxField };
