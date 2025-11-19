import { writeFileSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

import {
  OpenAPIRegistry,
  OpenApiGeneratorV3,
  extendZodWithOpenApi,
} from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

// Step 2: Import and register each resource's paths
import { registerAppConfigPaths } from '@/openapi/paths/app-config';
import { registerBackersPaths } from '@/openapi/paths/backers';
import { registerCampaignPaths } from '@/openapi/paths/campaigns';
import { registerCategoriesPaths } from '@/openapi/paths/categories';
import { registerDonationsPaths } from '@/openapi/paths/donations';
import { registerDonorsPaths } from '@/openapi/paths/donors';
import { registerFundraisersPaths } from '@/openapi/paths/fundraisers';
import { registerMediaPaths } from '@/openapi/paths/media';
import { registerOptionsPaths } from '@/openapi/paths/options';
import { registerPledgesPaths } from '@/openapi/paths/pledges';
import { registerRewardItemsPaths } from '@/openapi/paths/reward-items';
import { registerRewardsPaths } from '@/openapi/paths/rewards';
import { registerTagsPaths } from '@/openapi/paths/tags';
import { registerValidateEmailPaths } from '@/openapi/paths/validate-email';
import { registerWPPagesPaths } from '@/openapi/paths/wp-pages';

extendZodWithOpenApi(z);

// Step 1: Create registry
const registry = new OpenAPIRegistry();

registerCampaignPaths(registry);
registerAppConfigPaths(registry);
registerOptionsPaths(registry);
registerWPPagesPaths(registry);
registerMediaPaths(registry);
registerValidateEmailPaths(registry);
registerCategoriesPaths(registry);
registerTagsPaths(registry);
registerRewardsPaths(registry);
registerRewardItemsPaths(registry);
registerBackersPaths(registry);
registerFundraisersPaths(registry);
registerPledgesPaths(registry);
registerDonationsPaths(registry);
registerDonorsPaths(registry);

// Step 3: Generate and write spec
const generator = new OpenApiGeneratorV3(registry.definitions);
const document = generator.generateDocument({
  openapi: '3.0.0',
  info: {
    title: 'Growfund API',
    version: '1.0.0',
    description: 'Auto-generated OpenAPI 3.0 spec for Growfund plugin',
  },
});

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const outputPath = path.join(__dirname, '../../public/openapi.json');

writeFileSync(outputPath, JSON.stringify(document, null, 2));
