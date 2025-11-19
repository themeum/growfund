import { type FieldValues } from 'react-hook-form';

import { Editor, type EditorSettingsProps } from '@/components/ui/editor';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface EditorFieldProps<T extends FieldValues> extends ControllerField<T> {
  rows?: number;
  cols?: number;
  shortCodes?: { label: string; value: string }[];
  settings?: EditorSettingsProps;
  toolbar1?: string;
  toolbar2?: string;
  isMinimal?: boolean;
}

function EditorField<T extends FieldValues>({
  control,
  name,
  label,
  placeholder,
  description,
  disabled = false,
  readOnly = false,
  inline = false,
  rows,
  cols,
  className,
  autoFocus = false,
  shortCodes,
  settings,
  toolbar1,
  toolbar2,
  isMinimal,
}: EditorFieldProps<T>) {
  return (
    <FormField
      control={control}
      name={name}
      render={({ field }) => {
        return (
          <FormItem className={cn(inline ? 'growfund-flex growfund-items-baseline gap-4' : 'growfund-space-y-2')}>
            {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
            <div className="growfund-space-y-2 growfund-w-full">
              <FormControl>
                <Editor
                  {...field}
                  autoFocus={autoFocus}
                  rows={rows}
                  cols={cols}
                  readOnly={readOnly}
                  disabled={disabled}
                  placeholder={placeholder}
                  className={cn(className)}
                  shortCodes={shortCodes}
                  settings={settings}
                  isMinimal={isMinimal}
                  toolbar1={toolbar1}
                  toolbar2={toolbar2}
                />
              </FormControl>
              {isDefined(description) && <FormDescription>{description}</FormDescription>}
              <FormMessage />
            </div>
          </FormItem>
        );
      }}
    ></FormField>
  );
}

export { EditorField };
