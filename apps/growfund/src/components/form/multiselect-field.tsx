import { __ } from '@wordpress/i18n';
import { Check, ChevronsUpDown, PlusCircle, X } from 'lucide-react';
import { useCallback, useMemo, useState } from 'react';
import { type FieldValues } from 'react-hook-form';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { type Option } from '@/types';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface SelectFieldProps<T extends FieldValues, V extends string | number>
  extends Omit<ControllerField<T>, 'readOnly'> {
  options: Option<V>[];
  allowCreate?: boolean;
  onCreateOption?: (value: string, triggerComplete: () => void) => void;
  allowDelete?: boolean;
  onDeleteOption?: (option: Option<V>) => void;
  loading?: boolean;
}

function purifyValue(value: string[], options: Option<string>[]) {
  return value.filter((item) => !!options.find((option) => option.value === item));
}

function MultiSelectField<T extends FieldValues, V extends string | number>({
  control,
  name,
  label,
  description,
  options,
  disabled = false,
  className,
  placeholder = __('Select an option', 'growfund'),
  allowCreate = false,
  onCreateOption,
  allowDelete = false,
  onDeleteOption,
  loading = false,
}: SelectFieldProps<T, V>) {
  const [inputValue, setInputValue] = useState('');
  const [open, setOpen] = useState(false);

  const optionsToDisplay = useMemo(() => {
    return options.filter((option) =>
      option.label.toLowerCase().includes(inputValue.toLowerCase()),
    );
  }, [inputValue, options]);

  const handleValueChange = useCallback((previous: string[], current: string) => {
    return previous.includes(current)
      ? previous.filter((value) => String(value) !== String(current))
      : [...previous, current];
  }, []);

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        const fieldValue = field.value
          ? purifyValue(field.value as string[], options as Option<string>[])
          : [];
        const showCreateButton =
          allowCreate && inputValue.length > 0 && optionsToDisplay.length === 0;

        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}

            <div className={cn('growfund-space-y-2', className)}>
              <FormControl>
                <div
                  className={cn(
                    'growfund-border growfund-border-border growfund-rounded-md',
                    'focus-within:growfund-outline-none focus-within:growfund-ring-2 focus-within:growfund-ring-ring focus-within:growfund-ring-offset-2',
                    fieldState.error &&
                      'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
                  )}
                >
                  <Popover open={open} onOpenChange={setOpen}>
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        className={cn(
                          'growfund-w-full growfund-justify-start growfund-py-1 growfund-px-3 growfund-text-fg-subdued hover:growfund-text-fg-subdued growfund-font-regular growfund-cursor-text hover:growfund-bg-transparent growfund-border-none focus-visible:growfund-ring-0 focus-visible:growfund-ring-offset-0',
                          fieldState.error && 'growfund-bg-background-fill-critical-secondary',
                        )}
                        disabled={disabled}
                      >
                        {placeholder}
                        <ChevronsUpDown className="growfund-ml-auto growfund-opacity-50" />
                      </Button>
                    </PopoverTrigger>

                    {fieldValue.length > 0 && (
                      <Separator
                        className={cn(fieldState.error && 'growfund-bg-background-fill-critical')}
                      />
                    )}

                    <PopoverContent className="growfund-w-[var(--radix-popover-trigger-width)] growfund-p-0">
                      <Command className="growfund-w-full">
                        <CommandInput
                          placeholder={placeholder}
                          value={inputValue}
                          onValueChange={setInputValue}
                          onKeyDown={(event) => {
                            if (allowCreate && event.key === 'Enter') {
                              event.preventDefault();
                              event.stopPropagation();
                              onCreateOption?.(inputValue, () => {
                                setInputValue('');
                                field.onChange(handleValueChange(fieldValue, inputValue));
                                setOpen(false);
                              });
                            }
                          }}
                        />
                        <CommandList>
                          <CommandEmpty className="growfund-py-2">
                            {showCreateButton ? (
                              <Button
                                variant="link"
                                onClick={() => {
                                  onCreateOption?.(inputValue, () => {
                                    setInputValue('');
                                    field.onChange(handleValueChange(fieldValue, inputValue));
                                    setOpen(false);
                                  });
                                }}
                              >
                                {loading ? <LoadingSpinner /> : <PlusCircle />}
                                {__('Add item', 'growfund')}
                              </Button>
                            ) : (
                              <div className="growfund-w-full growfund-flex growfund-items-center growfund-justify-center">
                                {__('No results found', 'growfund')}
                              </div>
                            )}
                          </CommandEmpty>

                          <CommandGroup>
                            {optionsToDisplay.map((option) => (
                              <CommandItem
                                key={option.value}
                                value={String(option.value)}
                                keywords={[option.label]}
                                onSelect={(currentValue) => {
                                  field.onChange(handleValueChange(fieldValue, currentValue));
                                }}
                                className="growfund-group/item"
                              >
                                <Check
                                  className={cn(
                                    'growfund-size-4 growfund-transition-opacity',
                                    fieldValue.includes(String(option.value))
                                      ? 'growfund-opacity-100'
                                      : 'growfund-opacity-0',
                                  )}
                                />
                                {option.label}

                                {allowDelete && (
                                  <Button
                                    variant="ghost"
                                    size="icon"
                                    className="growfund-size-3 growfund-text-icon-secondary growfund-ms-auto hover:growfund-text-icon-secondary-hover growfund-transition-opacity growfund-opacity-0 group-hover/item:growfund-opacity-100"
                                    onClick={(event) => {
                                      event.stopPropagation();
                                      onDeleteOption?.(option);
                                    }}
                                  >
                                    <X />
                                  </Button>
                                )}
                              </CommandItem>
                            ))}
                          </CommandGroup>
                        </CommandList>
                      </Command>
                    </PopoverContent>
                  </Popover>
                  {fieldValue.length > 0 && (
                    <div className="growfund-flex growfund-flex-wrap growfund-gap-2 growfund-m-3">
                      {options
                        .filter((option) => fieldValue.includes(String(option.value)))
                        .map((option, index) => (
                          <Badge key={index} variant="secondary">
                            {option.label}
                            <Button
                              variant="ghost"
                              size="icon"
                              className="growfund-size-4 growfund-ml-1"
                              onClick={() => {
                                field.onChange(
                                  fieldValue.filter(
                                    (value) => String(value) !== String(option.value),
                                  ),
                                );
                              }}
                            >
                              <X className="growfund-text-icon-primary" />
                            </Button>
                          </Badge>
                        ))}
                    </div>
                  )}
                </div>
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

export { MultiSelectField };
