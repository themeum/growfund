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
import { LocalFileUploader } from '@/components/ui/local-file-uploader';
import { cn } from '@/lib/utils';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

interface LocalFileUploaderFieldProps<T extends FieldValues> extends Omit<
  ControllerField<T>,
  'readOnly' | 'placeholder'
> {
  uploadButtonLabel: string;
  maxSize?: number;
  accept?: MediaType[];
  isInline?: boolean;
  defaultFileName?: string | null;
  defaultFileUrl?: string;
  onRemove?: () => void;
}

function LocalFileUploaderField<T extends FieldValues>({
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
  defaultFileName,
  defaultFileUrl,
  onRemove,
}: LocalFileUploaderFieldProps<T>) {
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
              <LocalFileUploader
                file={field.value}
                onChange={(file) => {
                  field.onChange(file);

                  if (onRemove && !isDefined(file)) {
                    onRemove();
                  }
                }}
                isInline={isInline}
                disabled={disabled}
                maxSize={maxSize}
                uploadButtonLabel={uploadButtonLabel}
                dropzoneLabel={__('Click to upload a file from your device', 'growfund')}
                accept={accept}
                className={cn(fieldState.error && 'growfund-border-border-critical', className)}
                defaultFileName={defaultFileName}
                defaultFileUrl={defaultFileUrl}
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

export default LocalFileUploaderField;
