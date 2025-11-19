import { z } from 'zod';

import { MediaSchema } from '@/schemas/media';

const CategorySchema = z.object({
  id: z.coerce.string(),
  name: z.string(),
  slug: z.string().nullish(),
  image: MediaSchema.nullish(),
  description: z.string().nullish(),
  parent_id: z.string().nullish(),
  level: z.number(),
  count: z.number(),
  is_default: z.coerce.boolean().default(false),
});

const CategoryFormSchema = CategorySchema.omit({
  id: true,
  level: true,
  count: true,
  is_default: true,
});

const CategoryPayloadSchema = CategoryFormSchema;
const CategoryResponseSchema = CategorySchema;

type Category = z.infer<typeof CategorySchema>;
type CategoryPayload = z.infer<typeof CategoryPayloadSchema>;
type CategoryForm = z.infer<typeof CategoryFormSchema>;

export {
  CategoryFormSchema,
  CategoryPayloadSchema,
  CategoryResponseSchema,
  type Category,
  type CategoryForm,
  type CategoryPayload,
};
