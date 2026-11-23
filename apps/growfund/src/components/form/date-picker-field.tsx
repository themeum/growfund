import { CalendarIcon } from '@radix-ui/react-icons';
import { __, sprintf } from '@wordpress/i18n';
import { formatDate } from 'date-fns';
import { useState } from 'react';
import { type DateRange } from 'react-day-picker';
import { type FieldValues } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
  Dialog,
  DialogCloseButton,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { ScrollArea } from '@/components/ui/scroll-area';
import CalendarPresets from '@/features/campaigns/components/additional/calender-presets';
import { useIsMobile } from '@/hooks/use-mobile';
import { DATE_FORMATS, isDateRange } from '@/lib/date';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface DatePickerFieldProps<T extends FieldValues> extends ControllerField<T> {
  type?: 'single' | 'range';
  clearable?: boolean;
  showRangePresets?: boolean;
  popoverSide?: 'top' | 'bottom' | 'left' | 'right';
}

interface CalendarBodyProps {
  type: 'single' | 'range';
  showRangePresets?: boolean;
  onChange: (value: DateRange | Date) => void;
  value: DateRange | Date;
}

interface ClearButtonProps {
  isDisabled?: boolean;
  onClear: () => void;
}
const CalendarBody = ({ type, showRangePresets, onChange, value }: CalendarBodyProps) => {
  const [month, setMonth] = useState<Date>();
  const [activeRangeItem, setActiveRangeItem] = useState<string>();

  return (
    <div className="growfund-flex growfund-gap-1">
      {showRangePresets && type === 'range' && (
        <div className="growfund-hidden md:growfund-block">
          <CalendarPresets
            activeRangeItem={activeRangeItem}
            setActiveRangeItem={setActiveRangeItem}
            setMonth={setMonth}
            onRangeChange={(newValue) => {
              onChange(newValue);
            }}
          />
        </div>
      )}

      {type === 'range' ? (
        <Calendar
          mode="range"
          month={month}
          onMonthChange={setMonth}
          selected={value as DateRange | undefined}
          onSelect={(value: unknown) => {
            const date = value as DateRange;
            onChange(date);
          }}
          numberOfMonths={2}
          initialFocus
        />
      ) : (
        <Calendar
          mode="single"
          month={month}
          onMonthChange={setMonth}
          selected={value as Date | undefined}
          onSelect={(value: unknown) => {
            const date = value as Date;
            onChange(date);
          }}
          numberOfMonths={1}
          classNames={{
            day_range_start: '[&]:growfund-text-fg-light growfund-day-range-start',
            day_range_end: '[&]:growfund-text-fg-light',
            day_range_middle:
              '[&]:growfund-text-fg-light aria-selected:growfund-bg-accent aria-selected:growfund-text-fg-accent hover:[&]:growfund-text-fg-primary',
            day_selected: '[&]:growfund-bg-background-fill-brand [&]:growfund-text-fg-light',
            nav: 'growfund-w-full growfund-flex growfund-gap-2 growfund-items-center growfund-justify-end',
          }}
          initialFocus
        />
      )}
    </div>
  );
};

const ClearButton = ({ isDisabled, onClear }: ClearButtonProps) => {
  return (
    <div className="growfund-w-full growfund-flex growfund-items-center growfund-justify-center growfund-my-4">
      <Button variant="secondary" size="sm" disabled={isDisabled} onClick={onClear}>
        {__('Clear Selection', 'growfund')}
      </Button>
    </div>
  );
};

function DatePickerField<T extends FieldValues>({
  control,
  name,
  label,
  type = 'single',
  placeholder = __('Pick a date', 'growfund'),
  description,
  disabled = false,
  className,
  clearable = false,
  showRangePresets = false,
  popoverSide,
}: DatePickerFieldProps<T>) {
  const [open, setOpen] = useState(false);
  const isMobile = useIsMobile();

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        const getDisplayText = () => {
          if (!field.value) {
            return <span>{placeholder}</span>;
          }

          if (isDateRange(field.value)) {
            const { from, to } = field.value as DateRange;
            if (!isDefined(from) && !isDefined(to)) {
              return <span>{placeholder}</span>;
            }
            if (isDefined(from) && isDefined(to)) {
              return sprintf(
                '%s - %s',
                formatDate(from, DATE_FORMATS.DATE_FIELD),
                formatDate(to, DATE_FORMATS.DATE_FIELD),
              );
            }
            if (isDefined(from) && !isDefined(to)) {
              return sprintf('%s - %s', formatDate(from, DATE_FORMATS.DATE_FIELD), 'YYYY/MM/DD');
            }
            return <span>{placeholder}</span>;
          }

          if (isDefined(field.value)) {
            return formatDate(field.value, DATE_FORMATS.DATE_FIELD);
          }

          return <span>{placeholder}</span>;
        };

        const isClearButtonDisabled =
          type === 'single'
            ? !isDefined(field.value)
            : !isDefined((field.value as DateRange | undefined)?.from);

        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
            <FormControl>
              {isMobile ? (
                <div>
                  <Button
                    variant="outline"
                    className={cn(
                      'growfund-w-full growfund-justify-start growfund-text-left growfund-font-normal growfund-px-3 hover:growfund-bg-background-surface growfund-bg-background-white',
                      fieldState.error &&
                        'growfund-border-border-critical growfund-bg-background-fill-critical-secondary growfund-text-fg-critical',
                      disabled && 'growfund-opacity-50',
                      className,
                    )}
                    onClick={() => {
                      setOpen(true);
                    }}
                    type="button"
                  >
                    <CalendarIcon />
                    {getDisplayText()}
                  </Button>
                  <Dialog open={open} onOpenChange={setOpen}>
                    <DialogContent
                      className={cn(
                        '!growfund-fixed !growfund-top-1/2 !growfund-left-1/2',
                        '!-growfund-translate-x-1/2 !-growfund-translate-y-1/2',
                        '!growfund-w-[90vw] !growfund-h-[90vh]',
                        '!growfund-max-w-none !growfund-max-h-none',
                        '!growfund-rounded-2xl',
                        '!growfund-p-0 !growfund-m-0 growfund-flex growfund-flex-col',
                      )}
                    >
                      <DialogHeader>
                        <DialogTitle>
                          {type === 'range'
                            ? __('Select Date Range', 'growfund')
                            : __('Select Date', 'growfund')}
                        </DialogTitle>
                        <DialogCloseButton />
                      </DialogHeader>

                      <ScrollArea className="growfund-flex-1 growfund-px-2 growfund-pt-2">
                        <CalendarBody
                          type={type}
                          showRangePresets={showRangePresets}
                          onChange={(date) => {
                            field.onChange(date);
                            if (type === 'single' && isDefined(date)) {
                              setOpen(false);
                            }
                            if (type === 'range') {
                              const range = date as DateRange;
                              if (range.from && range.to) {
                                setOpen(false);
                              }
                            }
                          }}
                          value={field.value}
                        />
                      </ScrollArea>
                    </DialogContent>
                  </Dialog>
                </div>
              ) : (
                <Popover open={open} onOpenChange={setOpen}>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      type="button"
                      onClick={() => {
                        setOpen(true);
                      }}
                      className={cn(
                        'growfund-w-full growfund-justify-start growfund-text-left growfund-font-normal growfund-px-3',
                        fieldState.error &&
                          'growfund-border-border-critical growfund-bg-background-fill-critical-secondary growfund-text-fg-critical',
                        disabled && 'growfund-opacity-50',
                        className,
                      )}
                    >
                      <CalendarIcon />
                      {getDisplayText()}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent
                    className="growfund-w-auto growfund-p-0"
                    align="center"
                    side={popoverSide}
                    avoidCollisions={true}
                    collisionPadding={16}
                  >
                    <CalendarBody
                      type={type}
                      showRangePresets={showRangePresets}
                      onChange={(date) => {
                        field.onChange(date);
                        if (type === 'single' && isDefined(date)) {
                          setOpen(false);
                        }
                        if (type === 'range') {
                          const range = date as DateRange;
                          if (range.from && range.to) {
                            setOpen(false);
                          }
                        }
                      }}
                      value={field.value}
                    />

                    {clearable && (
                      <ClearButton
                        isDisabled={isClearButtonDisabled}
                        onClear={() => {
                          field.onChange(type === 'single' ? null : { from: null, to: null });
                          setOpen(false);
                        }}
                      />
                    )}
                  </PopoverContent>
                </Popover>
              )}
            </FormControl>
            {isDefined(description) && <FormDescription>{description}</FormDescription>}
            <FormMessage />
          </FormItem>
        );
      }}
    />
  );
}

export { DatePickerField };
