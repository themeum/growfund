import { z } from 'zod';

const TagFilterSchema = z.object({
  search: z.string().nullish(),
  action: z.enum(['delete']).nullish(),
});

type TagFilter = z.infer<typeof TagFilterSchema>;

export { TagFilterSchema, type TagFilter };
