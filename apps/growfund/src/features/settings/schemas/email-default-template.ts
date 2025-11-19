import { z } from 'zod';

import { TemplateMediaSchema } from '@/features/settings/schemas/template';


const EmailTemplateColorsSchema = z.object({
  background: z.string().nullish(),
  text: z.string().nullish(),
  link: z.string().nullish(),
  label: z.string().nullish(),
  button: z.string().nullish(),
  button_background: z.string().nullish(),
});

const EmailTemplateContentSchema = z.object({
  additional: z.string().nullish(),
  footer: z.string(),
});

const EmailTemplateSchema = z.object({
  media: TemplateMediaSchema.nullish(),
  content: EmailTemplateContentSchema.nullish(),
  colors: EmailTemplateColorsSchema.nullish(),
});

const EmailTemplateFormSchema = EmailTemplateSchema;

type EmailTemplateColors = z.infer<typeof EmailTemplateColorsSchema>;

type EmailTemplate = z.infer<typeof EmailTemplateSchema>;

type EmailTemplateForm = z.infer<typeof EmailTemplateFormSchema>;

export {
  EmailTemplateColorsSchema,
  EmailTemplateContentSchema,
  EmailTemplateFormSchema,
  EmailTemplateSchema,
  type EmailTemplate,
  type EmailTemplateColors,
  type EmailTemplateForm,
};
