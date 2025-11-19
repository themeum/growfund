import { z } from 'zod';

import { AlignmentSchema } from '@/schemas/alignment';
import { MediaSchema } from '@/schemas/media';

const TemplateMediaSchema = z.object({
  image: MediaSchema.nullish(),
  height: z.coerce.string().default('24').nullish(),
  position: AlignmentSchema,
});

type TemplateMedia = z.infer<typeof TemplateMediaSchema>;

export { TemplateMediaSchema, type TemplateMedia };
