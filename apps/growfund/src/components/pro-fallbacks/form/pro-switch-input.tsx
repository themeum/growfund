import { Edit } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { FormControl } from '@/components/ui/form';
import { Label } from '@/components/ui/label';
import { ProBadge } from '@/components/ui/pro-badge';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface ProSwitchInputProps {
  allowEdit?: boolean;
  hideToggle?: boolean;
  className?: string;
  label?: string;
  description?: string;
  showProBadge?: boolean;
}

function ProSwitchInput({
  label,
  description,
  className,
  allowEdit = false,
  hideToggle = false,
  showProBadge = false,
}: ProSwitchInputProps) {
  return (
    <div
      className={cn(
        'growfund-w-full growfund-flex growfund-items-center growfund-justify-between growfund-gap-4 growfund-group/switch',
        className,
      )}
    >
      <div className="growfund-space-y-2">
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
        {isDefined(description) && (
          <p className="growfund-text-[0.8rem] growfund-text-fg-muted">{description}</p>
        )}
      </div>
      <div className="growfund-flex growfund-items-center growfund-gap-2">
        {allowEdit && (
          <Button variant="ghost" size="icon">
            <Edit className="growfund-text-icon-primary" />
          </Button>
        )}
        {!hideToggle && (
          <FormControl>
            <Switch disabled={true} checked={false} aria-readonly />
          </FormControl>
        )}
      </div>
    </div>
  );
}

export { ProSwitchInput };
