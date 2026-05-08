import { __ } from '@wordpress/i18n';
import { type FieldValues, useFormContext } from 'react-hook-form';

import { FileUploader } from '@/components/ui/file-uploader';
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
import { MediaType } from '@/utils/media';

interface FileFieldProps<T extends FieldValues> extends Omit<
  ControllerField<T>,
  'readOnly' | 'placeholder'
> {
  uploadButtonLabel: string;
  maxSize?: number;
  accept?: MediaType[];
  isInline?: boolean;
}

function FileUploaderField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  uploadButtonLabel,
  accept = [MediaType.DOCUMENTS, MediaType.ZIP, MediaType.IMAGES],
  maxSize,
  isInline = false,
}: FileFieldProps<T>) {
  const form = useFormContext<T>();

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            <div className="growfund-h-4">
              <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>
            </div>

            <FormControl>
              <FileUploader
                isInline={isInline}
                value={field.value}
                onChange={field.onChange}
                disabled={disabled}
                maxSize={maxSize}
                uploadButtonLabel={uploadButtonLabel}
                dropzoneLabel={__('Drag and drop, or upload a file', 'growfund')}
                accept={accept}
                className={cn(fieldState.error && 'growfund-border-border-critical', className)}
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
    />
  );
}

export default FileUploaderField;
