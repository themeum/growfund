import { z } from 'zod';

const TagSchema = z.object({
  id: z.coerce.string(),
  name: z.string(),
  slug: z.string(),
  description: z.string().nullish(),
});

const TagResponseSchema = TagSchema.extend({
  count: z.number().nullish(),
});
const TagFormSchema = TagSchema.omit({ id: true, slug: true }).extend({
  slug: z.string().nullish(),
});
const TagPayloadSchema = TagFormSchema;

type Tag = z.infer<typeof TagResponseSchema>;
type TagForm = z.infer<typeof TagFormSchema>;
type TagPayload = z.infer<typeof TagPayloadSchema>;

export {
  TagFormSchema,
  TagPayloadSchema,
  TagResponseSchema,
  type Tag,
  type TagForm,
  type TagPayload,
};
