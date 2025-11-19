/* eslint-disable no-console */
import { RELEASE_DIR } from '../config/paths.js';
import { cleanupDirectory } from '../utils/filesystem.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Cleanup temporary files and directories
 */
export function cleanup(spinner?: LoadingSpinner): void {
  if (!spinner) {
    console.log('\n🧹 Cleaning up temporary files...');
  } else {
    spinner.update('Cleaning up temporary files...');
  }

  cleanupDirectory(RELEASE_DIR);

  if (!spinner) {
    console.log('✅ Cleanup completed');
  }
}
