import { __ } from '@wordpress/i18n';
import { useFormContext, useWatch } from 'react-hook-form';

import { CheckboxField } from '@/components/form/checkbox-field';
import { EditorField } from '@/components/form/editor-field';
import { MediaField } from '@/components/form/media-field';
import { type PdfReceiptTemplateForm } from '@/features/settings/schemas/pdf-receipt';
import { MediaType } from '@/utils/media';

const PdfAnnualReceiptContentForm = ({
  shortCodes,
}: {
  shortCodes?: { label: string; value: string }[];
}) => {
  const form = useFormContext<PdfReceiptTemplateForm>();
  const isSignatureAvailable = useWatch({
    control: form.control,
    name: 'content.signature.is_available',
  });

  return (
    <div className="growfund-w-full growfund-space-y-4">
      <EditorField
        control={form.control}
        name="content.greetings"
        label={__('Greetings', 'growfund')}
        isMinimal
        toolbar1="bold italic | alignleft aligncenter alignright | outdent indent | shortcode_button"
        shortCodes={shortCodes}
      />
      <CheckboxField
        control={form.control}
        name="content.signature.is_available"
        label={__('Add Signature', 'growfund')}
      />
      {isSignatureAvailable && (
        <>
          <MediaField
            label={__('Signature Image', 'growfund')}
            control={form.control}
            name="content.signature.image"
            uploadButtonLabel={__('Upload Image', 'growfund')}
            dropzoneLabel={__('Drag and drop, or upload image', 'growfund')}
            accept={[MediaType.IMAGES]}
          />
          <EditorField
            control={form.control}
            name="content.signature.details"
            label={__('Signature', 'growfund')}
            isMinimal
            toolbar1="bold italic"
            settings={{ tinymce: { autoresize_min_height: 50 } }}
          />
        </>
      )}
      <EditorField
        control={form.control}
        name="content.footer"
        label={__('Footer', 'growfund')}
        rows={4}
        isMinimal
        toolbar1="bold italic | alignleft aligncenter alignright | outdent indent"
      />
    </div>
  );
};

export default PdfAnnualReceiptContentForm;
