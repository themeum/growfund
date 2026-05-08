import { __ } from '@wordpress/i18n';
import { z } from 'zod';

import { isDefined } from '@/utils';

const FundraiserPayoutTypeSchema = z.enum(['iban', 'usd', 'cad', 'aud', 'beftn', 'gbp']);
const FundraiserPayoutPaymentMethodSchema = z.enum(['bank', 'paypal', 'others']);
const FundraiserPayoutSettingsSchema = z.object({
  payment_method: FundraiserPayoutPaymentMethodSchema.default('bank'),
  type: FundraiserPayoutTypeSchema.nullish(),
  account_holder_name: z.string().nullish(),
  account_number: z.string().nullish(),
  swift_bic: z.string().nullish(),
  bank_details_document: z
    .object({
      file_name: z.string().nullish(),
    })
    .nullish(),
  state_province: z.string().nullish(),
  city: z.string().nullish(),
  zip_code: z.string().nullish(),
  address: z.string().nullish(),
  institution_code: z.string().nullish(),
  transit_code: z.string().nullish(),
  province: z.string().nullish(),
  bsb_code: z.string().nullish(),
  bank_name: z.string().nullish(),
  branch_name: z.string().nullish(),
  routing_number: z.string().nullish(),
  district: z.string().nullish(),
  sort_code: z.string().nullish(),
  information: z.string().nullish(),
  email: z.string().nullish(),
});
const FundraiserPayoutSettingsFormSchema = FundraiserPayoutSettingsSchema.merge(
  z.object({
    bank_details_document: z
      .object({
        file: z.instanceof(File).or(z.string()).nullish(),
      })
      .nullish(),
  }),
).superRefine((data, ctx) => {
  const validateType = () => {
    switch (data.type) {
      case 'iban':
        if (!data.account_holder_name) {
          ctx.addIssue({
            path: ['account_holder_name'],
            message: __('Account Holder Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.account_number) {
          ctx.addIssue({
            path: ['bank_account_number'],
            message: __('Bank Account Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.swift_bic) {
          ctx.addIssue({
            path: ['bic_swift_code'],
            message: __('BIC / SWIFT Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!isDefined(data.bank_details_document?.file)) {
          ctx.addIssue({
            path: ['bank_details_document.file'],
            message: __('Bank details document is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        break;
      case 'usd':
        if (!data.account_holder_name) {
          ctx.addIssue({
            path: ['account_holder_name'],
            message: __('Account Holder Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.account_number) {
          ctx.addIssue({
            path: ['account_number'],
            message: __('Account Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.routing_number) {
          ctx.addIssue({
            path: ['routing_number'],
            message: __('Routing Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.swift_bic) {
          ctx.addIssue({
            path: ['bic_swift_code'],
            message: __('BIC / SWIFT Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        break;
      case 'aud':
        if (!data.account_holder_name) {
          ctx.addIssue({
            path: ['account_holder_name'],
            message: __('Account Holder Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.account_number) {
          ctx.addIssue({
            path: ['account_number'],
            message: __('Account Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.bsb_code) {
          ctx.addIssue({
            path: ['bsb_code'],
            message: __('BSB Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.state_province) {
          ctx.addIssue({
            path: ['state_province'],
            message: __('State is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.city) {
          ctx.addIssue({
            path: ['city'],
            message: __('City is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.zip_code) {
          ctx.addIssue({
            path: ['zip_code'],
            message: __('Zip/Postal Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.address) {
          ctx.addIssue({
            path: ['address'],
            message: __('Address is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        break;
      case 'cad':
        if (!data.account_holder_name) {
          ctx.addIssue({
            path: ['account_holder_name'],
            message: __('Account Holder Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.account_number) {
          ctx.addIssue({
            path: ['account_number'],
            message: __('Account Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.institution_code) {
          ctx.addIssue({
            path: ['institution_code'],
            message: __('Institution Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.transit_code) {
          ctx.addIssue({
            path: ['transit_code'],
            message: __('Transit Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        break;
      case 'beftn':
        if (!data.account_holder_name) {
          ctx.addIssue({
            path: ['account_holder_name'],
            message: __('Account Holder Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.bank_name) {
          ctx.addIssue({
            path: ['bank_name'],
            message: __('Bank Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.branch_name) {
          ctx.addIssue({
            path: ['branch_name'],
            message: __('Branch Name is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.account_number) {
          ctx.addIssue({
            path: ['account_number'],
            message: __('Account Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.routing_number) {
          ctx.addIssue({
            path: ['routing_number'],
            message: __('Routing Number is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.address) {
          ctx.addIssue({
            path: ['street_address'],
            message: __('Street Address is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.city) {
          ctx.addIssue({
            path: ['city'],
            message: __('City is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.district) {
          ctx.addIssue({
            path: ['district'],
            message: __('District / Division is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        if (!data.zip_code) {
          ctx.addIssue({
            path: ['zip_code'],
            message: __('Zip / Postal Code is required', 'growfund-pro'),
            code: z.ZodIssueCode.custom,
          });
        }
        break;
    }
  };

  switch (data.payment_method) {
    case 'bank':
      validateType();
      break;
    case 'paypal':
      if (!data.email) {
        ctx.addIssue({
          path: ['email'],
          message: __('Email is required for PayPal', 'growfund-pro'),
          code: z.ZodIssueCode.custom,
        });
      }
      break;
    case 'others':
      if (!data.information) {
        ctx.addIssue({
          path: ['information'],
          message: __('Information is required for other payment methods', 'growfund-pro'),
          code: z.ZodIssueCode.custom,
        });
      }
      break;
  }
});

type PayoutSettings = z.infer<typeof FundraiserPayoutSettingsSchema>;
type PayoutSettingsForm = z.infer<typeof FundraiserPayoutSettingsFormSchema>;
type PayoutType = z.infer<typeof FundraiserPayoutTypeSchema>;
type PayoutPaymentMethod = z.infer<typeof FundraiserPayoutPaymentMethodSchema>;

export {
  FundraiserPayoutPaymentMethodSchema,
  FundraiserPayoutSettingsFormSchema,
  FundraiserPayoutSettingsSchema,
  FundraiserPayoutTypeSchema,
  type PayoutPaymentMethod,
  type PayoutSettings,
  type PayoutSettingsForm,
  type PayoutType,
};
