/* eslint-disable no-console */
import { existsSync, rmSync } from 'fs';
import { join } from 'path';

import { MAIN_PLUGIN_NAME, RELEASE_DIR, ROOT_DIR } from '../config/paths.js';
import { execCommand, execCommandAsync } from '../utils/executor.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Create zip file from the prepared plugin directory
 */
export async function createZipFile(
  version: string,
  outputDir?: string,
  spinner?: LoadingSpinner,
  pluginName?: string,
): Promise<string> {
  if (!spinner) {
    console.log('\n📦 Creating plugin zip file...');
  } else {
    spinner.update('Creating plugin zip file...');
  }

  pluginName = pluginName ?? MAIN_PLUGIN_NAME;

  const finalOutputDir = outputDir ?? ROOT_DIR;
  const zipFileName = `${pluginName}.zip`;
  const zipPath = join(finalOutputDir, zipFileName);

  // Remove existing zip if it exists
  if (existsSync(zipPath)) {
    rmSync(zipPath);
  }

  if (spinner) {
    spinner.update(`Compressing ${pluginName}-v${version} files...`);
  }

  const zipCommand = `zip -r "${zipPath}" ${pluginName}/`;

  if (spinner) {
    await execCommandAsync(zipCommand, RELEASE_DIR, spinner);
  } else {
    execCommand(zipCommand, RELEASE_DIR, false);
  }

  // Verify zip file was created
  if (existsSync(zipPath)) {
    try {
      const stats = execCommand(`ls -la "${zipPath}"`, finalOutputDir, true);
      if (!spinner) {
        console.log(`📦 Zip file created: ${zipFileName}`);
        console.log(`📊 File details: ${stats}`);
      }
    } catch {
      if (!spinner) {
        console.log(`📦 Zip file created: ${zipFileName}`);
      }
    }
    return zipPath;
  } else {
    throw new Error('Failed to create zip file');
  }
}
