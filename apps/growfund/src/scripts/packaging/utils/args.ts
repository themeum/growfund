/* eslint-disable no-console */
import { resolve } from 'path';

import type { PackageOptions } from '../types/options.js';

/**
 * Parse command line arguments and return package options
 */
export function parseArguments(): PackageOptions {
  const args = process.argv.slice(2);
  const options: PackageOptions = {};

  for (let i = 0; i < args.length; i++) {
    const arg = args[i];

    switch (arg) {
      case '--version':
      case '-v':
        options.mainVersion = args[++i];
        break;
      case '--skip-build':
        options.skipBuild = true;
        break;
      case '--skip-cleanup':
        options.skipCleanup = true;
        break;
      case '--output-dir':
      case '-o':
        options.outputDir = resolve(args[++i]);
        break;
      case '--help':
      case '-h':
        showHelp();
        process.exit(0);
    }
  }

  return options;
}

/**
 * Display help information
 */
function showHelp(): void {
  console.log(`
Usage: tsx src/scripts/make-package.ts [options]

Options:
  --version, -v <version>    Specify version (default: auto-detect from plugin file)
  --skip-build              Skip the build step (use existing build)
  --skip-cleanup            Don't cleanup temporary files after packaging
  --output-dir, -o <dir>    Output directory for zip file (default: project root)
  --help, -h                Show this help message

Examples:
  tsx src/scripts/make-package.ts
  tsx src/scripts/make-package.ts --version 1.2.3
  tsx src/scripts/make-package.ts --skip-build --output-dir ./releases
  tsx src/scripts/make-package.ts --skip-cleanup
        `);
}
