import { __ } from '@wordpress/i18n';
import { useEffect, type PropsWithChildren } from 'react';
import {
    useFormContext,
    useWatch,
    type Control,
    type FieldValues,
    type Path,
    type PathValue,
    type UseFormReturn,
} from 'react-hook-form';

import AlignmentSelectorField from '@/components/form/alignment-selector-field';
import { ColorPickerField } from '@/components/form/color-picker-field';
import { MediaField } from '@/components/form/media-field';
import RangeSliderField from '@/components/form/range-slider-field';
import { Box, BoxContent, BoxTitle } from '@/components/ui/box';
import { Form } from '@/components/ui/form';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import { MediaType } from '@/utils/media';

interface TemplateFormProps<TFields extends FieldValues>
  extends Omit<React.HTMLAttributes<HTMLFormElement>, 'onSubmit'> {
  ref: React.RefObject<HTMLFormElement | null>;
  form: UseFormReturn<TFields>;
}

const TemplateForm = <TFields extends FieldValues>({
  ref,
  className,
  form,
  children,
  ...props
}: TemplateFormProps<TFields>) => {
  return (
    <Form {...form}>
      <form ref={ref} className={cn(className)} {...props}>
        {children}
      </form>
    </Form>
  );
};

interface TemplateFormImageSectionProps<TValues extends FieldValues> {
  header: string;
  description?: string;
  control: Control<TValues>;
  namePrefix?: Path<TValues>;
  minRangeHeight?: number;
  maxRangeHeight?: number;
}
const TemplateFormImageSection = <TValues extends FieldValues>({
  header,
  description,
  control,
  namePrefix,
  minRangeHeight = 12,
  maxRangeHeight = 80,
}: TemplateFormImageSectionProps<TValues>) => {
  const form = useFormContext<TValues>();

  const height = useWatch({
    control,
    name: (namePrefix ? `${namePrefix}.height` : 'height') as Path<TValues>,
  });

  useEffect(() => {
    if (!isDefined(height) || height < minRangeHeight) {
      form.setValue(
        (namePrefix ? `${namePrefix}.height` : 'height') as Path<TValues>,
        String(minRangeHeight) as PathValue<TValues, Path<TValues>>,
      );
    }
  }, [form, height, minRangeHeight, namePrefix]);

  return (
    <Box className="growfund-p-4">
      <BoxTitle>{header}</BoxTitle>
      {isDefined(description) && (
        <p className="growfund-typo-small growfund-text-fg-secondary growfund-mt-1">{description}</p>
      )}
      <BoxContent className="growfund-p-0 growfund-mt-4 growfund-space-y-6">
        <MediaField
          control={control}
          name={namePrefix ? (`${namePrefix}.image` as Path<TValues>) : ('image' as Path<TValues>)}
          uploadButtonLabel={__('Upload Image', 'growfund')}
          dropzoneLabel={__('Drag and drop, or upload image', 'growfund')}
          accept={[MediaType.IMAGES]}
        />
        <RangeSliderField
          control={control}
          name={
            namePrefix ? (`${namePrefix}.height` as Path<TValues>) : ('height' as Path<TValues>)
          }
          label="Height"
          min={minRangeHeight}
          max={maxRangeHeight}
        />
        <AlignmentSelectorField
          control={control}
          name={
            namePrefix ? (`${namePrefix}.position` as Path<TValues>) : ('position' as Path<TValues>)
          }
          label="Position"
        />
      </BoxContent>
    </Box>
  );
};

interface TemplateFormContentProps {
  header: string;
  description?: string;
  className?: string;
}
const TemplateFormContentSection = ({
  children,
  header,
  description,
  className,
}: PropsWithChildren<TemplateFormContentProps>) => {
  return (
    <Box className="growfund-p-4">
      <BoxTitle>{header}</BoxTitle>
      {isDefined(description) && (
        <p className="growfund-typo-small growfund-text-fg-secondary growfund-mt-1">{description}</p>
      )}
      <BoxContent className={cn('growfund-p-0 growfund-mt-4', className)}>{children}</BoxContent>
    </Box>
  );
};

interface TemplateFormColorSectionProps<TValues extends FieldValues> {
  header: string;
  description?: string;
  fields: {
    name: Path<TValues>;
    label: string;
  }[];
  control: Control<TValues>;
}
const TemplateFormColorSection = <TValues extends FieldValues>({
  fields,
  header,
  description,
  control,
}: TemplateFormColorSectionProps<TValues>) => {
  return (
    <Box className="growfund-p-4">
      <BoxTitle>{header}</BoxTitle>
      {isDefined(description) && (
        <p className="growfund-typo-small growfund-text-fg-secondary growfund-mt-1">{description}</p>
      )}
      <BoxContent className="growfund-p-0 growfund-mt-4 growfund-space-y-4">
        {fields.map((field) => (
          <ColorPickerField
            key={field.name}
            control={control}
            name={field.name}
            label={field.label}
          />
        ))}
      </BoxContent>
    </Box>
  );
};

export {
    TemplateForm,
    TemplateFormColorSection,
    TemplateFormContentSection,
    TemplateFormImageSection
};

