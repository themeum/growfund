import { Edit } from 'lucide-react';
import { type FieldValues } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Switch } from '@/components/ui/switch';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

type SwitchFieldProps<T extends FieldValues> = Omit<
  ControllerField<T>,
  'readOnly' | 'placeholder' | 'inline'
> & {
  allowEdit?: boolean;
  onEdit?: () => void;
  allowHoverEffect?: boolean;
  hideToggle?: boolean;
};

function SwitchField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  allowEdit = false,
  onEdit,
  allowHoverEffect = false,
  hideToggle = false,
}: SwitchFieldProps<T>) {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field }) => {
        return (
          <FormItem
            className={cn(
              'growfund-w-full growfund-flex growfund-items-center growfund-justify-between growfund-gap-4 growfund-group/switch',
              allowHoverEffect && 'hover:growfund-bg-background-surface-secondary growfund-rounded-lg growfund-p-2',
            )}
          >
            <div className="growfund-space-y-2">
              {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
            <div className="growfund-flex growfund-items-center growfund-gap-2">
              {allowEdit && (
                <Button
                  variant="ghost"
                  size="icon"
                  onClick={onEdit}
                  className={cn(
                    allowHoverEffect &&
                      'growfund-opacity-0 growfund-transition-opacity group-hover/switch:growfund-opacity-100',
                  )}
                >
                  <Edit className="growfund-text-icon-primary" />
                </Button>
              )}
              {!hideToggle && (
                <FormControl>
                  <Switch
                    {...field}
                    disabled={disabled}
                    className={className}
                    checked={!!field.value}
                    onCheckedChange={field.onChange}
                    aria-readonly
                  />
                </FormControl>
              )}
            </div>
          </FormItem>
        );
      }}
    ></FormField>
  );
}

export { SwitchField };
