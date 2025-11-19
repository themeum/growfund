import { z } from 'zod';

import { TemplateMediaSchema } from '@/features/settings/schemas/template';

const EcardTemplateColorsSchema = z.object({
  background: z.string().nullish(),
  text_color: z.string().nullish(),
  secondary_text_color: z.string().nullish(),
  greetings: z.string().nullish(),
});

const EcardTemplateContentSchema = z.object({
  greetings: z.string(),
  description: z.string(),
});

const EcardTemplateSchema = z.object({
  media: TemplateMediaSchema.default({
    image: null,
    height: '326',
    position: 'center',
  }),
  content: EcardTemplateContentSchema,
  colors: EcardTemplateColorsSchema,
  short_codes: z.array(z.object({ value: z.string(), label: z.string() })).optional(),
});

const EcardTemplateFormSchema = EcardTemplateSchema.omit({ short_codes: true });

type EcardTemplateColors = z.infer<typeof EcardTemplateColorsSchema>;

type EcardTemplate = z.infer<typeof EcardTemplateSchema>;

type EcardTemplateForm = z.infer<typeof EcardTemplateFormSchema>;

export {
  EcardTemplateColorsSchema,
  EcardTemplateContentSchema,
  EcardTemplateFormSchema,
  EcardTemplateSchema,
  type EcardTemplate,
  type EcardTemplateColors,
  type EcardTemplateForm,
};
