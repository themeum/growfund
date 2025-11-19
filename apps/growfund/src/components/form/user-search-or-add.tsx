import { __, sprintf } from '@wordpress/i18n';
import { CommandEmpty, CommandList } from 'cmdk';
import { PlusCircleIcon, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { type FieldValues } from 'react-hook-form';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Command, CommandInput, CommandItem } from '@/components/ui/command';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormMessage,
} from '@/components/ui/form';
import { Popover, PopoverAnchor, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface UserOption {
  id: string;
  first_name: string;
  last_name: string;
  email: string;
  image?: MediaAttachment | null;
}

interface FieldProps<T extends FieldValues> extends Omit<ControllerField<T>, 'readOnly'> {
  emptyString?: string;
  selectedValue?: string | null;
  onSelectChange?: (value?: string | null) => void;
  showAddButton?: boolean;
  onClickAddButton?: (event: React.MouseEvent<HTMLButtonElement>) => void;
  searchValue?: string;
  onSearchChange?: (search: string) => void;
  options: UserOption[];
  showClearBtn?: boolean;
  loading?: boolean;
}

const UserSearchOrAddField = <T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  placeholder = __('Search or Add donor', 'growfund'),
  emptyString = __('No Results found', 'growfund'),
  showAddButton = true,
  selectedValue,
  onSelectChange,
  onClickAddButton,
  searchValue,
  onSearchChange,
  options,
  showClearBtn,
  loading = false,
}: FieldProps<T>) => {
  const searchRef = useRef<HTMLInputElement>(null);
  const [open, setOpen] = useState(false);

  const handleOnSelect = (value: string | null) => {
    onSearchChange?.('');
    setOpen(false);
    onSelectChange?.(value);
  };

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        const fieldValue = Array.isArray(field.value) ? (field.value as string[]) : [field.value];
        const selectedOption = options.find((option) => option.id === selectedValue);
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            <div>
              {isDefined(label) && (
                <span className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary growfund-mb-3">
                  {label}
                </span>
              )}
            </div>
            <FormControl>
              <Command
                className={cn(
                  'growfund-rounded-md growfund-border growfund-border-border growfund-w-full',
                  fieldState.error && 'growfund-border-border-critical',
                  className,
                )}
              >
                <Popover open={open} onOpenChange={setOpen}>
                  <PopoverAnchor>
                    <PopoverTrigger asChild>
                      {isDefined(selectedValue) && selectedValue !== '' ? (
                        <div className="growfund-flex growfund-py-2 growfund-px-3 growfund-items-center growfund-justify-between">
                          <div className="growfund-flex growfund-items-center growfund-gap-3">
                            <Avatar className="growfund-w-8 growfund-h-8">
                              <AvatarImage src={selectedOption?.image?.url} />
                            </Avatar>
                            <div className="growfund-typo-tiny growfund-flex growfund-flex-col growfund-gap-1">
                              <div className="growfund-text-fg-primary growfund-font-medium growfund-max-w-96">
                                {sprintf(
                                  '%s %s',
                                  selectedOption?.first_name,
                                  selectedOption?.last_name,
                                )}
                              </div>
                              <div className="growfund-text-fg-secondary growfund-max-w-96 growfund-truncate">
                                {selectedOption?.email}
                              </div>
                            </div>
                          </div>
                          {showClearBtn && (
                            <Button
                              variant="ghost"
                              size="icon"
                              className="hover:growfund-text-icon-critical"
                              onClick={() => {
                                handleOnSelect(null);
                              }}
                            >
                              <Trash2 />
                            </Button>
                          )}
                        </div>
                      ) : (
                        <CommandInput
                          ref={searchRef}
                          value={searchValue}
                          disabled={disabled}
                          placeholder={placeholder}
                          showSearchIcon={true}
                          onValueChange={onSearchChange}
                          onFocus={() => {
                            setOpen(true);
                          }}
                          onBlur={() => {
                            setOpen(false);
                          }}
                          onKeyDown={() => {
                            setOpen(true);
                          }}
                        />
                      )}
                    </PopoverTrigger>
                  </PopoverAnchor>
                  <PopoverContent
                    className="growfund-w-[var(--radix-popover-trigger-width)] growfund-p-0 growfund-max-h-64 growfund-overflow-auto growfund-relative"
                    onWheel={(event) => {
                      event.stopPropagation();
                    }}
                  >
                    {showAddButton && (
                      <div className="growfund-sticky growfund-top-0 growfund-z-20 growfund-flex growfund-border-b growfund-border-border growfund-gap-2 growfund-typo-small growfund-text-primary growfund-cursor-default growfund-bg-background-white">
                        <Button
                          variant="link"
                          className="growfund-w-28"
                          onClick={(event) => onClickAddButton?.(event)}
                          disabled={loading}
                        >
                          {loading ? (
                            <LoadingSpinner />
                          ) : (
                            <PlusCircleIcon className="growfund-size-4 growfund-text-icon-primary" />
                          )}

                          {__('Add new', 'growfund')}
                        </Button>
                      </div>
                    )}
                    <CommandList>
                      <CommandEmpty className="growfund-p-3">{emptyString}</CommandEmpty>
                      {options
                        .filter((option) => !fieldValue.includes(option.id))
                        .map((option) => {
                          const fullName = sprintf('%s %s', option.first_name, option.last_name);
                          return (
                            <CommandItem
                              className="growfund-px-3 growfund-cursor-pointer"
                              key={option.id}
                              keywords={[fullName]}
                              value={option.id}
                              onMouseDown={(event) => {
                                event.preventDefault();
                                event.stopPropagation();
                              }}
                              onSelect={(currentValue) => {
                                handleOnSelect(currentValue);
                              }}
                            >
                              <div className="growfund-flex growfund-gap-3 growfund-items-center">
                                <Avatar>
                                  <AvatarImage src={option.image?.url} />
                                  <AvatarFallback>{fullName.charAt(0)}</AvatarFallback>
                                </Avatar>
                                <div className="growfund-typo-tiny growfund-flex growfund-flex-col growfund-gap-1">
                                  <div className="growfund-text-fg-primary growfund-font-medium growfund-max-w-96">
                                    {fullName}
                                  </div>
                                  <div
                                    className="growfund-text-fg-secondary growfund-max-w-96 growfund-truncate"
                                    title={option.email}
                                  >
                                    {option.email}
                                  </div>
                                </div>
                              </div>
                            </CommandItem>
                          );
                        })}
                    </CommandList>
                  </PopoverContent>
                </Popover>
              </Command>
            </FormControl>
            {isDefined(description) && <FormDescription>{description}</FormDescription>}
            <FormMessage />
          </FormItem>
        );
      }}
    ></FormField>
  );
};
export { UserSearchOrAddField };
