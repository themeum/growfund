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
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';

import Video from './video';

interface VideoFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'readOnly' | 'placeholder'> {
  uploadButtonLabel?: string;
  dropzoneLabel?: string;
}

function VideoField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  uploadButtonLabel = __('Upload video', 'growfund'),
  dropzoneLabel = __('MP4 and WebM formats', 'growfund'),
}: VideoFieldProps<T>) {
  const form = useFormContext<T>();

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            <div className="growfund-h-5">
              <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>
            </div>
            <FormControl>
              <Video
                video={field.value}
                onChange={field.onChange}
                disabled={disabled}
                className={cn(fieldState.error && 'growfund-border-border-critical', className)}
                uploadButtonLabel={uploadButtonLabel}
                dropzoneLabel={dropzoneLabel}
                onError={(error) => {
                  if (!isDefined(error)) {
                    form.clearErrors(name);
                    return;
                  }

                  form.setError(name, {
                    type: 'manual',
                    message: error || '',
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

export default VideoField;
