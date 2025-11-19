import { Cross2Icon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import React from 'react';
import { HexColorInput, HexColorPicker } from 'react-colorful';

import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';

interface ColorPickerProps {
  defaultValue?: string;
  color?: string;
  onChange: (color: string) => void;
  closePopover?: () => void;
}

const ColorPicker = React.forwardRef<
  HTMLDivElement,
  React.ComponentProps<'div'> & ColorPickerProps
>(({ className, color, defaultValue, onChange, closePopover, ...props }, ref) => {
  return (
    <>
      <div
        ref={ref}
        className={cn(
          'growfund-color-picker-layout growfund-bg-background-surface growfund-rounded-md growfund-shadow-md',
          className,
        )}
        {...props}
      >
        <div className="growfund-flex growfund-justify-between growfund-items-center growfund-p-4">
          <span className="growfund-typo-small growfund-font-semibold">{__('Color Picker', 'growfund')}</span>
          <Button size="icon" variant="ghost" onClick={closePopover} className="growfund-size-6">
            <Cross2Icon />
          </Button>
        </div>
        <Separator />
        <div className="growfund-space-y-4 growfund-p-4">
          <HexColorPicker color={color ?? defaultValue} onChange={onChange} />
          <HexColorInput
            className="growfund-w-full growfund-p-1 growfund-border growfund-border-border growfund-rounded-sm focus-visible:growfund-outline-none"
            prefixed
            color={color ?? defaultValue}
            onChange={onChange}
          />
        </div>
      </div>
    </>
  );
});

export default ColorPicker;
