import { __, sprintf } from '@wordpress/i18n';
import { ListFilter, X } from 'lucide-react';
import { type UseQueryStatesKeysMap, type Values } from 'nuqs';
import React from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { getObjectKeys } from '@/utils';
import { parseFilterParams } from '@/utils/filters';

interface ActiveFiltersProps extends React.HTMLAttributes<HTMLDivElement> {
  params: Values<UseQueryStatesKeysMap>;
  onClear: (key: keyof UseQueryStatesKeysMap) => void;
  onClearAll: (newParams: Values<UseQueryStatesKeysMap>) => void;
  keyMap?: Record<keyof UseQueryStatesKeysMap, string>;
  valueMap?: Record<keyof UseQueryStatesKeysMap, (value: string) => string>;
}

const ActiveFilters = React.forwardRef<HTMLDivElement, ActiveFiltersProps>(
  ({ params, keyMap, valueMap, onClear, onClearAll, className, ...props }, ref) => {
    const appliedFilters = parseFilterParams(params, keyMap, valueMap);

    if (appliedFilters.length === 0) {
      return null;
    }

    return (
      <div
        ref={ref}
        className={cn(
          'growfund-bg-background-surface-secondary/60 growfund-rounded-md growfund-px-4 growfund-py-2 growfund-flex growfund-items-center growfund-gap-2',
          className,
        )}
        {...props}
      >
        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-shrink-0">
          <ListFilter className="growfund-size-4 growfund-text-icon-secondary" />
          <span className="growfund-typo-small growfund-text-fg-secondary">
            {__('Active filters:', 'growfund')}
          </span>
        </div>

        <div className="growfund-flex growfund-items-center growfund-gap-2 growfund-flex-wrap">
          {appliedFilters.map((filter, index) => {
            return (
              <Badge variant="secondary" key={index}>
                <span
                  className="growfund-max-w-40 growfund-truncate"
                  title={sprintf('%s: %s', filter.key_label, filter.value)}
                >
                  {sprintf('%s: %s', filter.key_label, filter.value)}
                </span>
                <Button
                  variant="ghost"
                  size="icon"
                  className="growfund-size-4 growfund-text-icon-secondary"
                  onClick={() => {
                    onClear(filter.key);
                  }}
                >
                  <X />
                </Button>
              </Badge>
            );
          })}
        </div>

        <Button
          variant="ghost"
          size="sm"
          className="growfund-py-0"
          onClick={() => {
            const availableKeys = getObjectKeys(params).filter((key) => key !== 'pg');

            const clearedParams = availableKeys.reduce<Values<UseQueryStatesKeysMap>>(
              (acc, key) => {
                acc[key as string] = undefined;
                return acc;
              },
              {},
            );
            onClearAll(clearedParams);
          }}
        >
          {__('Clear all', 'growfund')}
        </Button>
      </div>
    );
  },
);

export default ActiveFilters;
