import { Cross2Icon } from '@radix-ui/react-icons';
import { sprintf } from '@wordpress/i18n';
import { Check, Edit3, Globe } from 'lucide-react';
import { useRef, useState } from 'react';
import { type FieldValues } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface SlugFieldProps<T extends FieldValues> extends ControllerField<T> {
  noErrorMessage?: boolean;
  autoFocusVisible?: boolean;
  permalinkUrl: string;
}

function SlugField<T extends FieldValues>({
  control,
  name,
  placeholder,
  description,
  disabled = false,
  readOnly = false,
  inline = false,
  className,
  noErrorMessage = false,
  autoFocusVisible = false,
  permalinkUrl,
  ...props
}: SlugFieldProps<T>) {
  const [isSlugEditing, setIsSlugEditing] = useState(false);
  const [newSlug, setNewSlug] = useState('');
  const ref = useRef<HTMLInputElement>(null);

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        const slug = String(field.value ?? '');
        return (
          <FormItem
            className={cn(
              'growfund-w-full',
              inline ? 'growfund-flex growfund-items-baseline growfund-gap-4' : 'growfund-space-y-2',
            )}
          >
            <div className="growfund-space-y-2 growfund-w-full">
              <FormControl>
                <div className="growfund-flex growfund-items-center growfund-gap-1 growfund-group/slug growfund-min-h-[3.25rem]">
                  <Globe size={16} className="growfund-text-icon-secondary" />
                  {isSlugEditing ? (
                    <>
                      <div className="growfund-typo-tiny growfund-text-fg-secondary">
                        {sprintf('%s/', permalinkUrl)}
                      </div>
                      <div className="growfund-flex growfund-items-center growfund-gap-2">
                        <Input
                          {...field}
                          ref={ref}
                          placeholder={placeholder}
                          type="text"
                          value={newSlug}
                          readOnly={readOnly}
                          autoFocusVisible={autoFocusVisible}
                          onChange={(event) => {
                            setNewSlug(event.target.value);
                          }}
                          autoComplete="off"
                          disabled={disabled}
                          className={cn(
                            'growfund-w-full growfund-border-b-1 growfund-border-b-icon-emphasis',
                            !!fieldState.error &&
                              'growfund-border-b-border-critical growfund-bg-background-fill-critical-secondary',
                            className,
                          )}
                          {...props}
                        />
                        <div className="growfund-flex growfund-items-center growfund-gap-1">
                          <Button
                            variant="ghost"
                            className="growfund-size-8 growfund-rounded-md"
                            onClick={() => {
                              const finalValue = newSlug
                                .replace(/[ _/]/g, '-')
                                .replace(/-*@-*/g, '-at-');
                              field.onChange(finalValue);
                              setNewSlug('');
                              setIsSlugEditing(false);
                            }}
                          >
                            <Check size={16} />
                          </Button>
                          <Button
                            variant="ghost"
                            className="growfund-size-8 growfund-rounded-md"
                            onClick={() => {
                              field.onChange(slug);
                              setNewSlug('');
                              setIsSlugEditing(false);
                            }}
                          >
                            <Cross2Icon className="growfund-size-4" />
                          </Button>
                        </div>
                      </div>
                    </>
                  ) : (
                    <>
                      <div className="growfund-typo-tiny growfund-text-fg-secondary growfund-max-w-96 growfund-truncate">
                        {sprintf('%s/%s', permalinkUrl, slug)}
                      </div>
                      <Button
                        variant="ghost"
                        className="growfund-size-6 growfund-px-2 growfund-rounded-md group-hover/slug:growfund-visible growfund-invisible"
                        onClick={() => {
                          setNewSlug(slug);
                          setIsSlugEditing(true);
                        }}
                      >
                        <Edit3 size={16} />
                      </Button>
                    </>
                  )}
                </div>
              </FormControl>

              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              {!noErrorMessage && <FormMessage />}
            </div>
          </FormItem>
        );
      }}
    />
  );
}

export { SlugField };
