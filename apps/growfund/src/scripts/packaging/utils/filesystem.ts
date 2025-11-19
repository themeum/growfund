/* eslint-disable no-console */
import { existsSync, mkdirSync, readFileSync, rmSync } from 'fs';
import { join } from 'path';

// eslint-disable-next-line import/order
import { MAIN_PLUGIN_PATH, PRO_PLUGIN_PATH, ROOT_DIR } from '../config/paths.js';
import { execCommandAsync } from './executor.js';
import type { LoadingSpinner } from './spinner.js';

/**
 * Get plugin version from the main plugin file
 */
export function getMainPluginVersion(): string {
  try {
    const pluginFile = join(MAIN_PLUGIN_PATH, 'growfund.php');
    const content = readFileSync(pluginFile, 'utf8');
    const versionMatch = /Version:\s*(.+)/.exec(content);
    if (versionMatch) {
      return versionMatch[1].trim();
    }
  } catch {
    // Ignore file read errors
  }

  // Default version
  return '1.0.0';
}

export function getProPluginVersion(): string {
  try {
    const pluginFile = join(PRO_PLUGIN_PATH, 'growfund-pro.php');
    const content = readFileSync(pluginFile, 'utf8');
    const versionMatch = /Version:\s*(.+)/.exec(content);
    if (versionMatch) {
      return versionMatch[1].trim();
    }
  } catch {
    // Ignore file read errors
  }

  // Default version
  return '1.0.0';
}

/**
 * Ensure a directory exists, create it if it doesn't
 */
export function ensureDirectoryExists(dir: string): void {
  if (!existsSync(dir)) {
    mkdirSync(dir, { recursive: true });
    console.log(`📁 Created directory: ${dir}`);
  }
}

/**
 * Remove a directory and all its contents
 */
export function cleanupDirectory(dir: string): void {
  if (existsSync(dir)) {
    rmSync(dir, { recursive: true, force: true });
    console.log(`🗑️  Removed directory: ${dir}`);
  }
}

/**
 * Copy directory contents asynchronously with spinner support
 */
export async function copyDirectory(
  source: string,
  destination: string,
  spinner?: LoadingSpinner,
): Promise<void> {
  if (spinner) {
    spinner.update('Copying plugin files...');
  }

  ensureDirectoryExists(destination);
  // await execCommandAsync(`cp -r "${source}"/* "${destination}"/`, ROOT_DIR, spinner);
  await execCommandAsync(
    `rsync -av \
  --exclude='.DS_Store' \
  --exclude='__MACOSX' \
  --exclude='.AppleDouble' \
  "${source}"/* "${destination}"/
    `,
    ROOT_DIR,
    spinner,
  );
}
