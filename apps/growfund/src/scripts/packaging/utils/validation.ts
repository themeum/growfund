/* eslint-disable no-console */
import { existsSync } from 'fs';

// eslint-disable-next-line import/order
import { REQUIRED_COMMANDS, REQUIRED_PATHS } from '../config/paths.js';
import { execCommand } from './executor.js';
import type { LoadingSpinner } from './spinner.js';

/**
 * Validate the environment before starting the packaging process
 */
export function validateEnvironment(spinner?: LoadingSpinner): void {
  if (!spinner) {
    console.log('\n🔍 Validating environment...');
  } else {
    spinner.update('Checking directories...');
  }

  // Check if required directories exist
  for (const { path, name } of REQUIRED_PATHS) {
    if (!existsSync(path)) {
      throw new Error(`${name} not found: ${path}`);
    }
    if (!spinner) {
      console.log(`✅ ${name}: ${path}`);
    }
  }

  if (spinner) {
    spinner.update('Checking required commands...');
  }

  // Check for required commands
  for (const cmd of REQUIRED_COMMANDS) {
    try {
      execCommand(`${cmd} --version`, undefined, true);
      if (!spinner) {
        console.log(`✅ ${cmd} is available`);
      }
    } catch {
      throw new Error(`${cmd} is not installed or not in PATH`);
    }
  }
}
