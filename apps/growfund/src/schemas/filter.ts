import { z } from 'zod';

const DateRangeSchema = z.object({ from: z.date().nullish(), to: z.date().nullish() });

type DateRange = z.infer<typeof DateRangeSchema>;

export { DateRangeSchema, type DateRange };
