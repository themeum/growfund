import { writeFileSync } from 'node:fs';
import path from 'path';

import { MAIN_PLUGIN_PATH } from '@/scripts/packaging/config/paths';

import { AppConfigKeys } from '../features/settings/context/settings-context';
import {
  AdvancedSettingsSchema,
  BrandingSettingsSchema,
  CampaignSettingsSchema,
  EmailSettingsSchema,
  GeneralSettingsSchema,
  PaymentSettingsSchema,
  PDFReceiptSettingsSchema,
  SecuritySettingsSchema,
  UserPermissionsSettingsSchema,
} from '../features/settings/schemas/settings';
import { getDefaults } from '../lib/zod';

function optionDefaults() {
  const generalDefaults = getDefaults(GeneralSettingsSchema._def.schema);
  const campaignDefaults = getDefaults(CampaignSettingsSchema);
  const userAndPermissionsDefaults = getDefaults(UserPermissionsSettingsSchema);
  const paymentDefaults = getDefaults(PaymentSettingsSchema);
  const receiptDefaults = getDefaults(PDFReceiptSettingsSchema);
  const emailAndNotificationsDefaults = getDefaults(EmailSettingsSchema);
  const securityDefaults = getDefaults(SecuritySettingsSchema._def.schema);
  const brandingDefaults = getDefaults(BrandingSettingsSchema);
  const advancedDefaults = getDefaults(AdvancedSettingsSchema);

  const outputPath = path.join(MAIN_PLUGIN_PATH, 'resources/data/default-options.json');

  writeFileSync(
    outputPath,
    JSON.stringify(
      {
        [AppConfigKeys.General]: generalDefaults,
        [AppConfigKeys.Campaign]: campaignDefaults,
        [AppConfigKeys.UserAndPermissions]: userAndPermissionsDefaults,
        [AppConfigKeys.Payment]: paymentDefaults,
        [AppConfigKeys.Receipt]: receiptDefaults,
        [AppConfigKeys.EmailAndNotifications]: emailAndNotificationsDefaults,
        [AppConfigKeys.Security]: securityDefaults,
        [AppConfigKeys.Branding]: brandingDefaults,
        [AppConfigKeys.Advanced]: advancedDefaults,
      },
      null,
      2,
    ),
  );
}

const functions = {
  optionDefaults,
} as const;

const targetFunction = process.argv[2] as keyof typeof functions | undefined;

if (targetFunction) {
  functions[targetFunction]();
}

export { optionDefaults };
