import { __ } from '@wordpress/i18n';

import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ProBadge } from '@/components/ui/pro-badge';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface ProColorPickerInputProps {
  placeholder?: string;
  description?: string;
  label?: string;
  className?: string;
  showProBadge?: boolean;
}
function ProColorPickerInput({
  label,
  placeholder = __('Pick a color', 'growfund'),
  description,
  className,
  showProBadge = false,
}: ProColorPickerInputProps) {
  return (
    <div className="growfund-w-full growfund-space-y-2 growfund-select-none">
      {isDefined(label) && (
        <Label
          className={cn(
            'growfund-text-fg-primary growfund-typo-small growfund-font-medium growfund-min-h-4 growfund-flex growfund-items-center growfund-gap-1 growfund-flex-shrink-0',
            !showProBadge && 'growfund-text-fg-subdued',
          )}
        >
          {label}
          {showProBadge && <ProBadge />}
        </Label>
      )}
      <Button
        variant="outline"
        className={cn(
          'growfund-w-full growfund-justify-start growfund-text-left growfund-font-normal growfund-px-3 growfund-text-muted-foreground growfund-opacity-50',
          className,
        )}
      >
        <div
          className="growfund-w-4 growfund-h-4 growfund-rounded-full growfund-border"
          style={{
            backgroundColor: '#FFFFFF',
          }}
        />
        <span>{placeholder}</span>
      </Button>

      {isDefined(description) && <p className="growfund-text-[0.8rem] growfund-text-fg-muted">{description}</p>}
    </div>
  );
}

export { ProColorPickerInput };
