import { Label } from '@/components/ui/label';
import { ProBadge } from '@/components/ui/pro-badge';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface ProRadioInputProps {
  options: string[];
  inline?: boolean;
  label?: string;
  description?: string;
  showProBadge?: boolean;
  disabled?: boolean;
  className?: string;
}

function ProRadioInput({
  label,
  description,
  disabled = false,
  className,
  inline = false,
  options,
  showProBadge = false,
}: ProRadioInputProps) {
  return (
    <div className={cn('growfund-w-full', inline && 'growfund-flex growfund-flex-col growfund-gap-1')}>
      <div className="growfund-space-y-1">
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
      <RadioGroup
        disabled={disabled}
        className={cn('growfund-mt-2', inline && 'growfund-flex growfund-items-center growfund-gap-4', className)}
      >
        {options.map((option, index) => {
          return (
            <div key={index} className="growfund-flex growfund-items-center growfund-space-x-2">
              <RadioGroupItem disabled value="" />
              <span className="growfund-text-fg-subdued growfund-typo-small growfund-font-medium growfund-min-h-4 growfund-flex growfund-items-center growfund-gap-1">
                {option}
              </span>
            </div>
          );
        })}
      </RadioGroup>
    </div>
  );
}

export { ProRadioInput };
