import { Check } from 'lucide-react';
import { type FieldValues } from 'react-hook-form';

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { type Option } from '@/types';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

type SelectionCardField<T extends FieldValues, V> = Omit<
  ControllerField<T>,
  'readonly' | 'placeholder' | 'inline'
> & {
  options: Option<V>[];
};

const SelectionCardField = <T extends FieldValues, V>({
  control,
  name,
  label,
  description,
  options,
  disabled = false,
  className,
}: SelectionCardField<T, V>) => {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className={cn('growfund-w-full')}>
            <div className="growfund-space-y-1">
              {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
            <FormControl className="growfund-mt-3">
              <div className={cn('growfund-flex growfund-items-center growfund-gap-4', className)}>
                {options.map((option, index) => {
                  const isActive = field.value === option.value;
                  return (
                    <button
                      key={index}
                      type="button"
                      className={cn(
                        'growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-w-full growfund-gap-3 growfund-py-6 growfund-border-2 growfund-border-border-tertiary growfund-bg-background-white growfund-rounded-md growfund-cursor-pointer growfund-relative',
                        'hover:growfund-border-border-hover',
                        'focus-visible:growfund-outline-none focus-visible:growfund-ring-2 focus-visible:growfund-ring-ring focus-visible:growfund-ring-offset-2',
                        '[&[data-active="true"]]:growfund-border-icon-brand',
                      )}
                      onClick={() => {
                        field.onChange(option.value);
                      }}
                      disabled={disabled}
                      data-active={isActive}
                    >
                      {option.icon}
                      <FormLabel
                        className={cn(
                          'growfund-cursor-pointer',
                          fieldState.error && 'growfund-text-fg-critical',
                        )}
                      >
                        {option.label}
                      </FormLabel>
                      {isActive && (
                        <div className="growfund-absolute growfund-top-2 growfund-left-2 growfund-size-5 growfund-bg-background-fill-brand growfund-rounded-full growfund-flex growfund-items-center growfund-justify-center">
                          <Check className="growfund-size-3 growfund-text-white" />
                        </div>
                      )}
                    </button>
                  );
                })}
              </div>
            </FormControl>
          </FormItem>
        );
      }}
    />
  );
};

export default SelectionCardField;
