import { __ } from '@wordpress/i18n';
import z from 'zod';

const AddressSchema = z.object({
  address: z.string({ message: __('Address line 1 is required', 'growfund') }),
  address_2: z.string().nullish(),
  city: z.string({ message: __('City is required', 'growfund') }),
  state: z.string().nullish(),
  zip_code: z.string({ message: __('Zip code is required', 'growfund') }),
  country: z.string({ message: __('Country is required', 'growfund') }),
});

type Address = z.infer<typeof AddressSchema>;

export { AddressSchema, type Address };
