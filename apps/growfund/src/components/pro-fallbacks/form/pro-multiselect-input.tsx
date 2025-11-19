import { __ } from '@wordpress/i18n';
import { ChevronsUpDown, X } from 'lucide-react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { ProBadge } from '@/components/ui/pro-badge';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface ProMultiSelectInputProps {
  label?: string;
  description?: string;
  className?: string;
  placeholder?: string;
  showProBadge?: boolean;
  options?: string[];
}

function ProMultiSelectInput({
  label,
  description,
  className,
  placeholder = __('Select an option', 'growfund'),
  showProBadge = false,
  options = [],
}: ProMultiSelectInputProps) {
  return (
    <div className="growfund-w-full growfund-space-y-2">
      {isDefined(label) && (
        <Label
          className={cn(
            'growfund-text-fg-primary growfund-typo-small growfund-font-medium growfund-min-h-4 growfund-flex growfund-items-center growfund-gap-1 growfund-flex-shrink-0',
            !showProBadge && 'growfund-text-fg-subdued',
          )}
        >
          {label} {showProBadge && <ProBadge />}
        </Label>
      )}

      <div className={cn('growfund-space-y-2', className)}>
        <div
          className={cn(
            'growfund-border growfund-border-border growfund-rounded-md',
            'focus-within:growfund-outline-none focus-within:growfund-ring-2 focus-within:growfund-ring-ring focus-within:growfund-ring-offset-2',
          )}
        >
          <Button
            variant="outline"
            className={cn(
              'growfund-w-full growfund-justify-start growfund-py-1 growfund-px-3 growfund-text-fg-subdued hover:growfund-text-fg-subdued growfund-font-regular growfund-cursor-text hover:growfund-bg-transparent growfund-border-none focus-visible:growfund-ring-0 focus-visible:growfund-ring-offset-0',
            )}
            disabled={true}
          >
            {placeholder}
            <ChevronsUpDown className="growfund-ml-auto growfund-opacity-50" />
          </Button>
          <Separator />
          <div className="growfund-flex growfund-flex-wrap growfund-gap-2 growfund-m-3">
            {options.map((option, index) => (
              <Badge key={index} variant="ghost" className="growfund-bg-background-surface-secondary">
                {option}
                <Button variant="ghost" size="icon" className="growfund-size-4 growfund-ml-1">
                  <X className="growfund-text-icon-disabled" />
                </Button>
              </Badge>
            ))}
          </div>
        </div>
        {isDefined(description) && (
          <p className="growfund-text-[0.8rem] growfund-text-fg-muted">{description}</p>
        )}
      </div>
    </div>
  );
}

export { ProMultiSelectInput };
