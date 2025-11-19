import React from 'react';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuShortcut,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface ThreeDotsOptionsProps<T> extends React.HTMLAttributes<HTMLDivElement> {
  options: {
    label: string;
    value: T;
    icon?: React.ElementType;
    command?: string;
    is_critical?: boolean;
    hidden?: boolean;
    disabled?: boolean;
  }[];
  ref?: React.RefObject<HTMLDivElement>;
  onOptionSelect?: (value: T) => void;
}

const ThreeDotsOptions = <T,>({
  children,
  options,
  className,
  onOptionSelect,
  ref,
  ...props
}: ThreeDotsOptionsProps<T>) => {
  if (options.filter((option) => !option.hidden).length === 0) {
    return null;
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>{children}</DropdownMenuTrigger>
      <DropdownMenuContent {...props} ref={ref} className={cn(className)}>
        <DropdownMenuGroup>
          {options.map((option) => {
            const Icon = option.icon ?? null;
            return (
              !option.hidden && (
                <DropdownMenuItem
                  key={String(option.value)}
                  onClick={() => {
                    if (isDefined(option.disabled) && option.disabled) {
                      return;
                    }

                    onOptionSelect?.(option.value);
                  }}
                  className={cn(option.is_critical && 'growfund-text-fg-critical')}
                  disabled={option.disabled ?? false}
                >
                  {Icon && <Icon />}
                  {option.label}
                  {option.command && <DropdownMenuShortcut>{option.command}</DropdownMenuShortcut>}
                </DropdownMenuItem>
              )
            );
          })}
        </DropdownMenuGroup>
      </DropdownMenuContent>
    </DropdownMenu>
  );
};

export default ThreeDotsOptions;
