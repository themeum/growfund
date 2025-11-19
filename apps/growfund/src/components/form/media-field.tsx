import { __ } from '@wordpress/i18n';
import { type FieldValues, useFormContext } from 'react-hook-form';

import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import Media from '@/components/ui/media';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

interface MediaFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'readOnly' | 'placeholder'> {
  uploadButtonLabel?: string;
  dropzoneLabel?: string;
  accept?: MediaType[];
}

function MediaField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  uploadButtonLabel = __('Upload media', 'growfund'),
  dropzoneLabel = __('Drag and drop, or upload images', 'growfund'),
  accept = [MediaType.IMAGES],
}: MediaFieldProps<T>) {
  const form = useFormContext<T>();

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            {isDefined(label) && (
              <div className="growfund-h-5">
                <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>
              </div>
            )}
            <FormControl>
              <Media
                value={field.value ?? null}
                onChange={(value) => {
                  field.onChange(value);
                }}
                disabled={disabled}
                className={cn(fieldState.error && 'growfund-border-border-critical', className)}
                uploadButtonLabel={uploadButtonLabel}
                dropzoneLabel={dropzoneLabel}
                accept={accept}
                onError={(error) => {
                  if (!isDefined(error)) {
                    form.clearErrors(name);
                    return;
                  }

                  form.setError(name, {
                    type: 'manual',
                    message: error,
                  });
                }}
              />
            </FormControl>
            {isDefined(description) && <FormDescription>{description}</FormDescription>}
            <FormMessage />
          </FormItem>
        );
      }}
    ></FormField>
  );
}

export { MediaField };
