import z from 'zod';

const WoocommerceConfigSchema = z.object({
  country: z.string(),
  currency: z.string(),
  currency_position: z.string(),
  decimal_separator: z.string(),
  thousand_separator: z.string(),
  decimal_places: z.number(),
});

type WoocommerceConfig = z.infer<typeof WoocommerceConfigSchema>;

export { WoocommerceConfigSchema, type WoocommerceConfig };
