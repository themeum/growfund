import { join, resolve } from 'path';

/**
 * Main Plugin name
 */
export const MAIN_PLUGIN_NAME = 'growfund';

/**
 * Pro Plugin name
 */
export const PRO_PLUGIN_NAME = 'growfund-pro';

/**
 * Directory paths for the packaging process
 * Note: We're in resources/ts, need to go up to project root
 */
export const ROOT_DIR = resolve(process.cwd(), '../');
export const MAIN_PLUGIN_PATH = join(ROOT_DIR, 'wordpress/wp-content/plugins/growfund');
export const PRO_PLUGIN_PATH = join(ROOT_DIR, 'wordpress/wp-content/plugins/growfund-pro');
export const RELEASE_DIR = join(ROOT_DIR, 'release');
export const TS_DIR = join(ROOT_DIR, 'apps');

/**
 * Files and directories to remove during packaging
 */
export const FILES_TO_REMOVE = [
  'composer.lock',
  'phpcs.xml',
  'phpcs.xml.dist',
  '.DS_Store',
  'resources/dist/.DS_Store',
  'resources/dist/openapi.json',
];

export const VENDOR_DIRS_TO_REMOVE = [
  'vendor/wp-coding-standards',
  'vendor/phpcompatibility',
  'vendor/dealerdirect',
  'vendor/squizlabs',
];

/**
 * Required commands for packaging
 */
export const REQUIRED_COMMANDS = ['yarn', 'composer'];

/**
 * Required directory paths that must exist
 */
export const REQUIRED_PATHS = [
  { path: ROOT_DIR, name: 'Project Root' },
  { path: MAIN_PLUGIN_PATH, name: 'Main Plugin Directory' },
  { path: PRO_PLUGIN_PATH, name: 'Pro Plugin Directory' },
  { path: TS_DIR, name: 'TypeScript Directory' },
];
