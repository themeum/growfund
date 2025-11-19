import { CheckIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import {
    endOfMonth,
    endOfYear,
    startOfMonth,
    startOfYear,
    subDays,
    subMonths,
    subYears,
} from 'date-fns';
import { type DateRange } from 'react-day-picker';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

interface CalendarPresetsProps {
  onRangeChange: (value: DateRange) => void;
  activeRangeItem?: string;
  setActiveRangeItem: (type: string) => void;
  setMonth: (date: Date) => void;
}

const CalendarPresets = ({
  onRangeChange,
  activeRangeItem,
  setActiveRangeItem,
  setMonth,
}: CalendarPresetsProps) => {
  const calendarPresets = [
    {
      type: 'today',
      label: __('Today', 'growfund'),
    },
    {
      type: 'yesterday',
      label: __('Yesterday', 'growfund'),
    },
    {
      type: 'last_seven_days',
      label: __('Last 7 days', 'growfund'),
    },
    {
      type: 'last_thirty_days',
      label: __('Last 30 days', 'growfund'),
    },
    {
      type: 'last_ninety_days',
      label: __('Last 90 days', 'growfund'),
    },
    {
      type: 'this_month',
      label: __('This month', 'growfund'),
    },
    {
      type: 'last_month',
      label: __('Last month', 'growfund'),
    },
    {
      type: 'this_year',
      label: __('This year', 'growfund'),
    },
    {
      type: 'last_year',
      label: __('Last year', 'growfund'),
    },
  ];

  const handleItemClick = (presetType: string) => {
    setActiveRangeItem(presetType);
    const today = new Date();
    const yesterday = subDays(today, 1);
    const dateMap = new Map([
      ['today', { from: today, to: undefined }],
      ['yesterday', { from: yesterday, to: undefined }],
      ['last_seven_days', { from: subDays(today, 6), to: today }],
      ['last_thirty_days', { from: subDays(today, 29), to: today }],
      ['last_ninety_days', { from: subDays(today, 89), to: today }],
      ['this_month', { from: startOfMonth(today), to: today }],
      [
        'last_month',
        { from: startOfMonth(subMonths(today, 1)), to: endOfMonth(subMonths(today, 1)) },
      ],
      ['this_year', { from: startOfYear(today), to: endOfYear(today) }],
      ['last_year', { from: startOfYear(subYears(today, 1)), to: endOfYear(subYears(today, 1)) }],
    ]);
    const value = dateMap.get(presetType) ?? { from: today, to: today };

    onRangeChange(value);
    setMonth(value.from);
  };

  return (
    <div className="growfund-flex growfund-gap-1 growfund-flex-col growfund-flex-wrap growfund-pl-3 growfund-pt-3 growfund-w-[169px]">
      {calendarPresets.map((item, index) => (
        <div key={index}>
          <Button
            variant="ghost"
            onClick={() => {
              handleItemClick(item.type);
            }}
            className={cn(
              'growfund-w-full growfund-text-left growfund-justify-between growfund-px-2',
              activeRangeItem === item.type
                ? 'growfund-bg-background-surface-secondary'
                : 'growfund-text-fg-primary',
            )}
          >
            {item.label}
            {activeRangeItem === item.type && <CheckIcon className="growfund-h-4 growfund-w-4" />}
          </Button>
        </div>
      ))}
    </div>
  );
};

export default CalendarPresets;
