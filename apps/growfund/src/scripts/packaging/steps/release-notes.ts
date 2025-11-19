/* eslint-disable no-console */
import { readFileSync, writeFileSync } from 'fs';
import { join } from 'path';

import { MAIN_PLUGIN_NAME, ROOT_DIR } from '../config/paths.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Generate release notes for the package
 */
export function generateReleaseNotes(
  version: string,
  outputDir?: string,
  spinner?: LoadingSpinner,
): string {
  if (!spinner) {
    console.log('\n📝 Generating release notes...');
  } else {
    spinner.update('Generating release notes...');
  }

  const finalOutputDir = outputDir ?? ROOT_DIR;
  const releaseNotesPath = join(finalOutputDir, 'release_notes.md');

  try {
    // Try to read version-specific changelog
    const changelogPath = join(ROOT_DIR, 'change-logs', `${version}.md`);
    const changelogContent = readFileSync(changelogPath, 'utf8').trim();

    const releaseNotes = `## Growfund Plugin v${version}

${changelogContent}

### Installation
1. Download the \`${MAIN_PLUGIN_NAME}-v${version}.zip\` file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the zip file and activate the plugin

### Requirements
- WordPress 5.9 or higher
- PHP 7.4 or higher

---
**Generated locally at ${new Date().toISOString()}**`;

    writeFileSync(releaseNotesPath, releaseNotes, 'utf8');
  } catch {
    // Fallback if changelog doesn't exist
    const releaseNotes = `## Growfund Plugin v${version}

### What's New
- Plugin release for version ${version}
- Built with latest dependencies
- Production-ready optimized build

### Installation
1. Download the \`${MAIN_PLUGIN_NAME}-v${version}.zip\` file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the zip file and activate the plugin

### Requirements
- WordPress 5.9 or higher
- PHP 7.4 or higher

---
**Generated locally at ${new Date().toISOString()}**`;

    writeFileSync(releaseNotesPath, releaseNotes, 'utf8');
  }

  if (!spinner) {
    console.log(`📝 Release notes generated: ${releaseNotesPath}`);
  }

  return releaseNotesPath;
}
