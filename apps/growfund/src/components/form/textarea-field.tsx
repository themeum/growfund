import { Braces } from 'lucide-react';
import { useId, useRef, useState } from 'react';
import { useController, type FieldValues } from 'react-hook-form';

import ManageEditorShortCodes from '@/components/editor/manage-editor-shortcodes';
import { Button } from '@/components/ui/button';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { Textarea } from '@/components/ui/textarea';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

interface TextareaFieldProps<T extends FieldValues> extends ControllerField<T> {
  rows?: number;
  cols?: number;
  shortCodes?: { label: string; value: string }[];
}

function TextareaField<T extends FieldValues>({
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
}: TextareaFieldProps<T>) {
  const textareaRef = useRef<HTMLTextAreaElement>(null);
  const shortcodePopoverRef = useRef<HTMLDivElement>(null);
  const textareaId = useId();
  const [openShortcodes, setOpenShortcodes] = useState(false);
  const { field } = useController({ control: control, name: name });

  return (
    <>
      {isDefined(shortCodes) && shortCodes.length > 0 && (
        <ManageEditorShortCodes
          popoverRef={shortcodePopoverRef}
          open={openShortcodes}
          onOpenChange={(open) => {
            setOpenShortcodes(open);
            if (open) return;

            if (textareaRef.current) {
              textareaRef.current.focus();
            }
          }}
          allShortcodes={shortCodes}
          onSelect={(shortcode) => {
            if (!textareaRef.current) return;

            const textarea = textareaRef.current;
            const oldValue = textarea.value;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const newValue = `${oldValue.substring(0, start)}${shortcode}${oldValue.substring(
              end,
            )}`;

            field.onChange(newValue);

            setTimeout(() => {
              textarea.focus();
              const newCursorPos = start + shortcode.length;
              textarea.setSelectionRange(newCursorPos, newCursorPos);
            }, 0);
          }}
          popoverContainer={document.querySelector(`#${textareaId}`)}
        />
      )}
      <FormField
        control={control}
        name={name}
        render={({ field, fieldState }) => {
          return (
            <FormItem className={cn(inline ? 'growfund-flex growfund-items-baseline gap-4' : 'growfund-space-y-2')}>
              {(isDefined(label) || isDefined(shortCodes)) && (
                <div className="growfund-flex growfund-items-center growfund-justify-between">
                  {isDefined(label) && <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>}
                  {isDefined(shortCodes) && shortCodes.length > 0 && (
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => {
                        setOpenShortcodes(true);
                      }}
                    >
                      <Braces className="growfund-text-icon-primary" />
                    </Button>
                  )}
                </div>
              )}
              <div className="growfund-space-y-2 growfund-w-full">
                <FormControl>
                  <Textarea
                    {...field}
                    id={textareaId}
                    ref={textareaRef}
                    placeholder={placeholder}
                    value={String(field.value ?? '')}
                    readOnly={readOnly}
                    rows={rows}
                    cols={cols}
                    onChange={(event) => {
                      field.onChange(event.target.value);
                    }}
                    autoFocus={autoFocus}
                    autoComplete="off"
                    disabled={disabled}
                    className={cn(
                      'growfund-bg-background-white',
                      !!fieldState.error &&
                        'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
                      className,
                    )}
                  />
                </FormControl>
                {isDefined(description) && <FormDescription>{description}</FormDescription>}
                <FormMessage />
              </div>
            </FormItem>
          );
        }}
      />
    </>
  );
}

export { TextareaField };
