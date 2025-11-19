import z from "zod";

const DonorStatsSchema = z.object({
  total_number_of_donations: z.number(),
  total_supported_campaigns: z.number(),
  total_contributions: z.number(),
  average_contributions: z.number(),
});

type DonorStats = z.infer<typeof DonorStatsSchema>;

export { DonorStatsSchema, type DonorStats };