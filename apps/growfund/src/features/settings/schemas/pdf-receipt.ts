import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { TemplateMediaSchema } from '@/features/settings/schemas/template';
import { MediaSchema } from '@/schemas/media';
import { isDefined } from '@/utils';

const PdfReceiptTemplateColorsSchema = z.object({
  background: z.string().nullish(),
  primary_text: z.string().nullish(),
  secondary_text: z.string().nullish(),
});

const PdfReceiptTemplateContentSchema = z.object({
  greetings: z.string(),
  signature: z
    .object({
      is_available: z.boolean().default(false),
      image: MediaSchema.nullish(),
      details: z.string().nullish(),
    })
    .nullish(),
  tax_information: z
    .object({
      is_available: z.boolean().default(false),
      details: z.string().nullish(),
    })
    .nullish(),
  footer: z.string().nullish(),
});

const PdfReceiptTemplateSchema = z.object({
  media: TemplateMediaSchema,
  content: PdfReceiptTemplateContentSchema,
  colors: PdfReceiptTemplateColorsSchema.nullish(),
  short_codes: z.array(z.object({ label: z.string(), value: z.string() })).optional(),
});

const PdfReceiptTemplateFormSchema = PdfReceiptTemplateSchema.omit({
  short_codes: true,
}).superRefine((data, ctx) => {
  if (data.content.signature?.is_available && !isDefined(data.content.signature.image)) {
    ctx.addIssue({
      path: ['data.content.signature.image'],
      code: z.ZodIssueCode.custom,
      message: __('Signature image is required.', 'growfund'),
    });
  }

  if (data.content.tax_information?.is_available && !!data.content.tax_information.details) {
    ctx.addIssue({
      path: ['data.content.tax_information.details'],
      code: z.ZodIssueCode.custom,
      message: __('Tax information required.', 'growfund'),
    });
  }
});

type PdfReceiptTemplateColors = z.infer<typeof PdfReceiptTemplateColorsSchema>;

type PdfReceiptTemplate = z.infer<typeof PdfReceiptTemplateSchema>;

type PdfReceiptTemplateForm = z.infer<typeof PdfReceiptTemplateFormSchema>;

export {
  PdfReceiptTemplateColorsSchema,
  PdfReceiptTemplateContentSchema,
  PdfReceiptTemplateFormSchema,
  PdfReceiptTemplateSchema,
  type PdfReceiptTemplate,
  type PdfReceiptTemplateColors,
  type PdfReceiptTemplateForm,
};
