import { z } from 'zod';

export const getPaginatedResponseSchema = <T extends z.ZodTypeAny>(itemSchema: T) =>
  z.object({
    results: z.array(itemSchema),
    total: z.number(),
    count: z.number(),
    per_page: z.number(),
    has_more: z.boolean(),
    current_page: z.number(),
  });
