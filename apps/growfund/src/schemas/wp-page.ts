import z from 'zod';

const WPPageSchema = z.object({
  id: z.coerce.string(),
  name: z.string(),
  slug: z.string(),
  parent_id: z.string().nullish(),
  url: z.string().url().nullish(),
});

type WPPage = z.infer<typeof WPPageSchema>;

export { WPPageSchema, type WPPage };
