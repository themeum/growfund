/* eslint-disable no-console */
import { existsSync, rmSync } from 'fs';
import { join } from 'path';

import {
  FILES_TO_REMOVE,
  MAIN_PLUGIN_NAME,
  MAIN_PLUGIN_PATH,
  RELEASE_DIR,
  VENDOR_DIRS_TO_REMOVE,
} from '../config/paths.js';
import { cleanupDirectory, copyDirectory, ensureDirectoryExists } from '../utils/filesystem.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Create release-ready plugin directory
 */
export async function createReleaseDirectory(
  spinner?: LoadingSpinner,
  pluginName?: string,
  pluginPath?: string,
): Promise<string> {
  if (!spinner) {
    console.log('\n📁 Preparing main plugin directory for release...');
  }

  pluginName = pluginName ?? MAIN_PLUGIN_NAME;
  pluginPath = pluginPath ?? MAIN_PLUGIN_PATH;

  // Create release directory
  const releasePluginDir = join(RELEASE_DIR, pluginName);
  ensureDirectoryExists(RELEASE_DIR);

  // Copy all plugin files
  await copyDirectory(pluginPath, releasePluginDir, spinner);

  if (spinner) {
    spinner.update(`Cleaning up ${pluginName} development files...`);
  } else {
    console.log(`🧹 Cleaning up ${pluginName} development files...`);
  }

  // Remove TypeScript source and config files
  const tsPath = join(releasePluginDir, 'resources/ts');
  cleanupDirectory(tsPath);

  // Remove PHP development files
  FILES_TO_REMOVE.forEach((file) => {
    const filePath = join(releasePluginDir, file);
    if (existsSync(filePath)) {
      rmSync(filePath);
      if (!spinner) {
        console.log(`🗑️ ${pluginName} files removed: ${file}`);
      }
    }
  });

  // Remove development-only vendor packages
  VENDOR_DIRS_TO_REMOVE.forEach((dir) => {
    const dirPath = join(releasePluginDir, dir);
    cleanupDirectory(dirPath);
  });

  if (!spinner) {
    console.log(`✅ ${pluginName} plugin directory prepared successfully`);
  }

  return releasePluginDir;
}
