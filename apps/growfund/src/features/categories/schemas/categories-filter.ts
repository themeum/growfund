import { z } from 'zod';

const CategoryFilterSchema = z.object({
  search: z.string().nullish(),
});

type CategoryFilter = z.infer<typeof CategoryFilterSchema>;

export { CategoryFilterSchema, type CategoryFilter };
