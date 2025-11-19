import { z } from 'zod';
const DonorSearchSchema = z.object({
  search: z.string().nullish(),
});
type DonorSearchForm = z.infer<typeof DonorSearchSchema>;
export { DonorSearchSchema, type DonorSearchForm };
