/* eslint-disable no-console */
import { readFileSync, writeFileSync } from 'fs';
import path from 'path';
const __dirname = path.dirname(new URL(import.meta.url).pathname);

const pluginPath = path.resolve(__dirname, '../../../../wordpress/wp-content/plugins');

const GROWFUND_PHP_PATH = path.join(pluginPath, './growfund/growfund.php');

function updateEnvMode(mode: 'development' | 'production') {
  try {
    const content = readFileSync(GROWFUND_PHP_PATH, 'utf8');

    const updatedContent = content.replace(
      /define\('GROWFUND_ENV_MODE',\s*'(development|production)'\);/,
      `define('GROWFUND_ENV_MODE', '${mode}');`,
    );

    writeFileSync(GROWFUND_PHP_PATH, updatedContent, 'utf8');
    console.log(`✅ Updated GROWFUND_ENV_MODE to '${mode}' in growfund.php`);
  } catch (error) {
    console.error('❌ Error updating GROWFUND_ENV_MODE:', error);
    process.exit(1);
  }
}

// Get the mode from command line arguments
const mode = process.argv[2] as 'development' | 'production' | undefined;

if (!mode || !['development', 'production'].includes(mode)) {
  console.error('❌ Please specify mode: development or production');
  console.log('Usage: tsx src/scripts/build-env.ts <development|production>');
  process.exit(1);
}

updateEnvMode(mode);
