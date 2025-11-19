import { RadioGroupItem } from '@radix-ui/react-radio-group';
import { AlignCenter, AlignLeft, AlignRight } from 'lucide-react';
import { type ReactNode } from 'react';
import { type FieldValues } from 'react-hook-form';

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { RadioGroup } from '@/components/ui/radio-group';
import { cn } from '@/lib/utils';
import { type Alignment } from '@/schemas/alignment';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

const options: {
  value: Alignment;
  label: string;
  icon: ReactNode;
}[] = [
  {
    value: 'left',
    label: 'Left',
    icon: <AlignLeft className="growfund-size-4" />,
  },
  {
    value: 'center',
    label: 'Center',
    icon: <AlignCenter className="growfund-size-4" />,
  },
  {
    value: 'right',
    label: 'Right',
    icon: <AlignRight className="growfund-size-4" />,
  },
];

interface AlignmentSelectorFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'placeholder'> {
  rootClassName?: string;
}

const AlignmentSelectorField = <T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  rootClassName,
}: AlignmentSelectorFieldProps<T>) => {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className={cn('growfund-w-full')}>
            <div className="growfund-space-y-2">
              {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
            <FormControl
              className={cn(
                'growfund-mt-3 growfund-w-full growfund-grid growfund-grid-cols-3 growfund-rounded-lg growfund-bg-background-surface-secondary growfund-p-1 growfund-gap-1',
                fieldState.error && 'growfund-border-border-critical',
                rootClassName,
              )}
            >
              <RadioGroup
                onValueChange={field.onChange}
                value={field.value ?? ''}
                disabled={disabled}
                className="growfund-w-full"
              >
                {options.map((option, index) => {
                  return (
                    <FormItem
                      key={index}
                      className="growfund-w-full growfund-flex growfund-flex-col growfund-justify-center growfund-items-center"
                    >
                      <FormControl>
                        <RadioGroupItem value={option.value} />
                      </FormControl>
                      <FormLabel
                        className={cn(
                          'growfund-w-full growfund-flex growfund-justify-center growfund-items-center growfund-typo-small growfund-font-medium growfund-cursor-pointer growfund-transition-all growfund-py-1 growfund-px-2 hover:growfund-bg-background-surface hover:growfund-rounded-sm hover:growfund-shadow-sm',
                          field.value === option.value &&
                            'growfund-bg-background-surface growfund-rounded-sm growfund-shadow-sm',
                          className,
                        )}
                      >
                        {option.icon}
                      </FormLabel>
                      <FormLabel className="growfund-sr-only">{option.label}</FormLabel>
                    </FormItem>
                  );
                })}
              </RadioGroup>
            </FormControl>
          </FormItem>
        );
      }}
    />
  );
};

export default AlignmentSelectorField;
