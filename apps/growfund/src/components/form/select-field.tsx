import { __ } from '@wordpress/i18n';
import React from 'react';
import { type FieldValues } from 'react-hook-form';

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { type Option } from '@/types';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface SelectFieldProps<T extends FieldValues, V>
  extends Omit<ControllerField<T>, 'readOnly' | 'onChange'> {
  options: Option<V>[];
  onChange?: (value: string) => void;
}

const SelectIcon = ({ icon }: { icon: React.ReactNode | string }) => {
  if (typeof icon === 'string') {
    return (
      <Image
        src={icon}
        alt={__('Select icon', 'growfund')}
        className="growfund-size-4 growfund-mr-2"
        fit="contain"
      />
    );
  }
  return <span className="growfund-mr-2">{icon}</span>;
};

function SelectField<T extends FieldValues, V>({
  control,
  name,
  label,
  description,
  options,
  disabled = false,
  className,
  placeholder = __('Select an option', 'growfund'),
  onChange,
}: SelectFieldProps<T, V>) {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
            <div className={cn('growfund-space-y-2', className)}>
              <FormControl>
                <Select
                  onValueChange={(value) => {
                    field.onChange(value);
                    onChange?.(value);
                  }}
                  value={field.value ?? ''}
                  disabled={disabled || options.length === 0}
                >
                  <FormControl>
                    <SelectTrigger
                      className={cn(
                        fieldState.error && 'growfund-border-border-critical',
                        '[&_span]:growfund-flex [&_span]:growfund-items-center',
                        className,
                      )}
                    >
                      <SelectValue placeholder={placeholder} />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    {options.map((option, index) => {
                      if (option.has_separator_next) {
                        return (
                          <React.Fragment key={index}>
                            <SelectItem
                              value={String(option.value)}
                              className={cn(
                                '[&>span]:growfund-flex [&>span]:growfund-items-center [&_svg]:growfund-size-4 [&_svg]:growfund-text-icon-primary',
                                option.is_critical &&
                                  'growfund-text-fg-critical [&_svg]:growfund-text-icon-critical',
                              )}
                            >
                              {option.icon && <SelectIcon icon={option.icon} />}
                              {option.label}
                            </SelectItem>
                            <SelectSeparator />
                          </React.Fragment>
                        );
                      }
                      return (
                        <SelectItem
                          key={index}
                          value={String(option.value)}
                          className={cn(
                            '[&>span]:growfund-flex [&>span]:growfund-items-center [&_svg]:growfund-size-4 [&_svg]:growfund-text-icon-primary',
                            option.is_critical &&
                              'growfund-text-fg-critical [&_svg]:growfund-text-icon-critical',
                          )}
                        >
                          {option.icon && <SelectIcon icon={option.icon} />}
                          {option.label}
                        </SelectItem>
                      );
                    })}
                  </SelectContent>
                </Select>
              </FormControl>
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
          </FormItem>
        );
      }}
    />
  );
}

export { SelectField };
