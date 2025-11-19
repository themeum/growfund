import { __, sprintf } from '@wordpress/i18n';
import { type FieldValues, useFormContext } from 'react-hook-form';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    FormControl,
    FormDescription,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { type ControllerField } from '@/types/form';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

import { GalleryProvider, useGalleryContext } from './gallery-context';
import ImageGallery from './image-gallery';

interface GalleryFieldProps<T extends FieldValues>
  extends Omit<ControllerField<T>, 'readOnly' | 'placeholder'> {
  uploadButtonLabel?: string;
  dropzoneLabel?: string;
  accept?: [MediaType.IMAGES];
}

function GalleryField<T extends FieldValues>({
  control,
  name,
  label,
  description,
  disabled = false,
  className,
  uploadButtonLabel = __('Upload images', 'growfund'),
  dropzoneLabel = __('Drag and drop, or upload images', 'growfund'),
  accept = [MediaType.IMAGES],
}: GalleryFieldProps<T>) {
  const form = useFormContext<T>();
  const { checkedItems, setCheckedItems } = useGalleryContext();

  return (
    <FormField
      control={control}
      name={name}
      render={({ field, fieldState }) => {
        return (
          <FormItem className="growfund-w-full growfund-space-y-2">
            <div className="growfund-h-5">
              {isDefined(label) && !checkedItems.length && (
                <FormLabel className="growfund-flex-shrink-0">{label}</FormLabel>
              )}
              {checkedItems.length > 0 && (
                <FormLabel className="growfund-flex growfund-justify-between growfund-w-full">
                  <div className="growfund-flex growfund-items-center growfund-gap-1">
                    <Checkbox
                      checked={checkedItems.length === field.value?.length ? true : 'indeterminate'}
                      onClick={() => {
                        setCheckedItems([]);
                      }}
                    />
                    {/* translators: %s: number of selected items */}
                    {sprintf(__('%d Selected', 'growfund'), checkedItems.length)}
                  </div>
                  <Button
                    variant="link"
                    onClick={() => {
                      field.onChange(
                        (field.value as MediaAttachment[]).filter((file: MediaAttachment) => {
                          return !checkedItems.includes(file.id);
                        }),
                      );
                      setCheckedItems([]);
                    }}
                    className="growfund-p-0 growfund-text-destructive growfund-h-auto"
                  >
                    {__('Remove', 'growfund')}
                  </Button>
                </FormLabel>
              )}
            </div>
            <FormControl>
              <ImageGallery
                value={field.value}
                onChange={field.onChange}
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

function GalleryFieldWithProvider<T extends FieldValues>(props: GalleryFieldProps<T>) {
  return (
    <GalleryProvider>
      <GalleryField {...props} />
    </GalleryProvider>
  );
}

export { GalleryFieldWithProvider as GalleryField };
