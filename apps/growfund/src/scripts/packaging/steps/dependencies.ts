/* eslint-disable no-console */
import { existsSync } from 'fs';

import { MAIN_PLUGIN_PATH, TS_DIR } from '../config/paths.js';
import { execCommand, execCommandAsync } from '../utils/executor.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Install Node.js dependencies using yarn
 */
export async function installNodeDependencies(spinner?: LoadingSpinner): Promise<void> {
  if (!spinner) {
    console.log('\n📦 Installing Node.js dependencies...');
  } else {
    spinner.update('Installing Node.js dependencies...');
  }

  // Verify the directory exists
  if (!existsSync(TS_DIR)) {
    throw new Error(`TypeScript directory not found: ${TS_DIR}`);
  }

  if (!spinner) {
    console.log(`📁 TS Directory: ${TS_DIR}`);
  }

  if (spinner) {
    await execCommandAsync('yarn install --frozen-lockfile', TS_DIR, spinner);
  } else {
    execCommand('yarn install --frozen-lockfile', TS_DIR, false);
  }

  if (!spinner) {
    console.log('✅ Node.js dependencies installed');
  }
}

/**
 * Build TypeScript/React application using yarn
 */
export async function buildTypeScriptApp(spinner?: LoadingSpinner): Promise<void> {
  if (!spinner) {
    console.log('\n🔨 Building TypeScript/React application...');
  } else {
    spinner.update('Building TypeScript application...');
  }

  if (spinner) {
    await execCommandAsync('yarn build:all', TS_DIR, spinner);
    await execCommandAsync('yarn make:pot', TS_DIR, spinner);
    await execCommandAsync('yarn make:pro-pot', TS_DIR, spinner);
  } else {
    execCommand('yarn build:all', TS_DIR, false);
    execCommand('yarn make:pot', TS_DIR, false);
    execCommand('yarn make:pro-pot', TS_DIR, false);
  }

  if (!spinner) {
    console.log('✅ TypeScript/React build completed successfully');
  }
}

/**
 * Install PHP dependencies using composer (production only)
 */
export async function installPHPDependencies(spinner?: LoadingSpinner): Promise<void> {
  if (!spinner) {
    console.log('\n🐘 Installing PHP dependencies (production only)...');
  } else {
    spinner.update('Installing PHP dependencies...');
  }

  // Verify the directory exists
  if (!existsSync(MAIN_PLUGIN_PATH)) {
    throw new Error(`Plugin directory not found: ${MAIN_PLUGIN_PATH}`);
  }

  if (!spinner) {
    console.log(`📁 Plugin Directory: ${MAIN_PLUGIN_PATH}`);
  }

  const composerCommand = 'composer install --no-dev --optimize-autoloader --no-interaction';

  if (spinner) {
    await execCommandAsync(composerCommand, MAIN_PLUGIN_PATH, spinner);
  } else {
    execCommand(composerCommand, MAIN_PLUGIN_PATH, false);
  }

  if (!spinner) {
    console.log('✅ PHP dependencies installed');
  }
}

export async function generateDefaultOptions(spinner?: LoadingSpinner) {
  if (!spinner) {
    console.log('\n🐘 Generating default options...');
  } else {
    spinner.update('Generating default options...');
  }

  await execCommandAsync('yarn extract:option-defaults', TS_DIR, spinner);
}
