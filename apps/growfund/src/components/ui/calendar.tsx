import { ChevronLeft, ChevronRight } from 'lucide-react';
import * as React from 'react';
import { DayPicker } from 'react-day-picker';

import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export type CalendarProps = React.ComponentProps<typeof DayPicker>;

function Calendar({ className, classNames, showOutsideDays = true, ...props }: CalendarProps) {
  return (
    <DayPicker
      showOutsideDays={showOutsideDays}
      className={cn('growfund-p-3', className)}
      classNames={{
        months: 'growfund-flex growfund-flex-col sm:growfund-flex-row growfund-space-y-4 sm:growfund-space-x-4 sm:growfund-space-y-0',
        month: 'growfund-space-y-4',
        caption: 'growfund-flex growfund-justify-center growfund-pt-1 growfund-relative growfund-items-center',
        caption_label: 'growfund-typo-small growfund-font-medium growfund-shrink-0',
        nav: 'growfund-absolute [&:has([name="previous-month"])]:growfund-left-0 [&:has([name="next-month"])]:growfund-right-0 growfund-p-1',
        nav_button: cn(
          buttonVariants({ variant: 'outline' }),
          'growfund-size-7 growfund-bg-transparent growfund-p-0 growfund-opacity-50 hover:growfund-opacity-100',
        ),
        table: 'growfund-w-full growfund-border-collapse growfund-space-y-1',
        head_row: 'growfund-flex',
        head_cell: 'growfund-text-fg-muted growfund-rounded-md growfund-w-8 growfund-font-normal growfund-text-[0.8rem]',
        row: 'growfund-flex growfund-w-full growfund-mt-2',
        cell: cn(
          'growfund-relative growfund-p-0 growfund-text-center growfund-typo-small focus-within:growfund-relative focus-within:growfund-z-20 [&:has([aria-selected])]:growfund-bg-accent [&:has([aria-selected].growfund-day-outside)]:growfund-bg-accent/50 [&:has([aria-selected].growfund-day-range-end)]:growfund-rounded-r-md',
          props.mode === 'range'
            ? '[&:has(>.growfund-day-range-end)]:growfund-rounded-r-md [&:has(>.growfund-day-range-start)]:growfund-rounded-l-md first:[&:has([aria-selected])]:growfund-rounded-l-md last:[&:has([aria-selected])]:growfund-rounded-r-md'
            : '[&:has([aria-selected])]:growfund-rounded-md',
        ),
        day: cn(
          buttonVariants({ variant: 'ghost' }),
          'growfund-h-8 growfund-w-8 growfund-p-0 growfund-font-normal aria-selected:growfund-opacity-100',
        ),
        day_range_start:
          'growfund-day-range-start growfund-bg-background-fill-brand growfund-text-fg-light hover:growfund-bg-background-fill-brand hover:growfund-text-fg-light focus:growfund-bg-background-fill-brand focus:growfund-text-fg-light',
        day_range_end:
          'growfund-day-range-end growfund-bg-background-fill-brand growfund-text-fg-light hover:growfund-bg-background-fill-brand hover:growfund-text-fg-light focus:growfund-bg-background-fill-brand focus:growfund-text-fg-light',
        day_range_middle: 'growfund-day-range-middle growfund-bg-background-fill-secondary growfund-text-fg-primary',
        day_selected: `growfund-bg-background-fill-brand growfund-text-fg-light hover:growfund-bg-background-fill-brand hover:growfund-text-fg-light focus:growfund-bg-background-fill-brand focus:growfund-text-fg-light`,
        day_today: 'growfund-bg-background-surface-secondary growfund-text-fg-primary',
        day_outside:
          'growfund-day-outside growfund-text-fg-muted aria-selected:growfund-bg-accent/50 aria-selected:growfund-text-fg-muted',
        day_disabled: 'growfund-text-fg-muted growfund-opacity-50',
        day_hidden: 'invisible',
        ...classNames,
      }}
      components={{
        IconLeft: ({ className, ...props }) => (
          <ChevronLeft className={cn('growfund-h-4 growfund-w-4', className)} {...props} />
        ),
        IconRight: ({ className, ...props }) => (
          <ChevronRight className={cn('growfund-h-4 growfund-w-4', className)} {...props} />
        ),
      }}
      {...props}
    />
  );
}
Calendar.displayName = 'Calendar';

export { Calendar };
