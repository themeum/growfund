/* eslint-disable no-console */
import { pathToFileURL } from 'url';

import {
  MAIN_PLUGIN_NAME,
  MAIN_PLUGIN_PATH,
  PRO_PLUGIN_NAME,
  PRO_PLUGIN_PATH,
  ROOT_DIR,
} from './config/paths.js';
import { cleanup } from './steps/cleanup.js';
import {
  buildTypeScriptApp,
  generateDefaultOptions,
  installNodeDependencies,
  installPHPDependencies,
} from './steps/dependencies.js';
import { packageAllGatewayPlugins } from './steps/gateway-plugins.js';
import { createZipFile } from './steps/package.js';
import { createReleaseDirectory } from './steps/prepare.js';
import { generateReleaseNotes } from './steps/release-notes.js';
import type { PackageOptions } from './types/options.js';
import { parseArguments } from './utils/args.js';
import { getMainPluginVersion, getProPluginVersion } from './utils/filesystem.js';
import { ProgressTracker } from './utils/spinner.js';
import { validateEnvironment } from './utils/validation.js';

/**
 * Main packaging orchestrator function
 */
export async function packagePlugin(options?: PackageOptions): Promise<{
  mainZipPath: string;
  proZipPath: string;
  releaseNotesPath: string;
  mainVersion: string;
  proVersion: string;
  gatewayPlugins: { pluginName: string; zipPath: string }[];
}> {
  console.log('🚀 Starting local plugin packaging process...\n');

  try {
    const finalOptions = options ?? parseArguments();
    const mainVersion = finalOptions.mainVersion ?? getMainPluginVersion();
    const proVersion = finalOptions.proVersion ?? getProPluginVersion();

    console.log(`📋 Package Configuration:`);
    console.log(`   Plugin Name: growfund`);
    console.log(`   Main Plugin Version: ${mainVersion}`);
    console.log(`   Pro Plugin Version: ${proVersion}`);
    console.log(`   Output Directory: ${finalOptions.outputDir ?? ROOT_DIR}`);
    console.log(`   Skip Build: ${finalOptions.skipBuild ?? false}`);
    console.log(`   Skip Cleanup: ${finalOptions.skipCleanup ?? false}`);
    console.log();

    // Initialize progress tracker
    const progress = new ProgressTracker();

    // Add all steps to the progress tracker
    progress.addStep('Validate Environment');
    progress.addStep('Install Node.js Dependencies');
    if (!finalOptions.skipBuild) {
      progress.addStep('Build TypeScript Application');
    }
    progress.addStep('Install PHP Dependencies');
    progress.addStep('Copy Main Plugin Files');
    progress.addStep('Copy Pro Plugin Files');
    progress.addStep('Create Main Zip Package');
    progress.addStep('Create Pro Zip Package');
    progress.addStep('Package Gateway Plugins');
    progress.addStep('Generate Release Notes');
    if (!finalOptions.skipCleanup) {
      progress.addStep('Cleanup');
    }

    // Step 1: Validate environment
    let spinner = progress.startStep('Validate Environment');
    try {
      validateEnvironment(spinner);
      progress.completeStep('Validate Environment');
      spinner.succeed('Environment validated');
    } catch (error) {
      spinner.fail('Environment validation failed');
      throw error;
    }

    // Step 2: Install Node.js dependencies
    spinner = progress.startStep('Install Node.js Dependencies');
    try {
      await installNodeDependencies(spinner);
      progress.completeStep('Install Node.js Dependencies');
      spinner.succeed('Node.js dependencies installed');
    } catch (error) {
      spinner.fail('Failed to install Node.js dependencies');
      throw error;
    }

    // Step 3: Build TypeScript/React application (unless skipped)
    if (!finalOptions.skipBuild) {
      spinner = progress.startStep('Build TypeScript Application');
      try {
        await buildTypeScriptApp(spinner);
        progress.completeStep('Build TypeScript Application');
        spinner.succeed('TypeScript application built');
      } catch (error) {
        spinner.fail('Failed to build TypeScript application');
        throw error;
      }
    } else {
      console.log('⏭️  Skipping TypeScript build (--skip-build flag)');
    }

    // Step 4: Install PHP dependencies
    spinner = progress.startStep('Install PHP Dependencies');
    try {
      await installPHPDependencies(spinner);
      progress.completeStep('Install PHP Dependencies');
      spinner.succeed('PHP dependencies installed');
    } catch (error) {
      spinner.fail('Failed to install PHP dependencies');
      throw error;
    }

    // Step 5: Generate default options
    spinner = progress.startStep('Generate Default Options');
    try {
      await generateDefaultOptions(spinner);
      progress.completeStep('Generate Default Options');
      spinner.succeed('Default options generated');
    } catch (error) {
      spinner.fail('Failed to generate default options');
      throw error;
    }

    // Step 6: Copy plugin files and prepare release directory
    spinner = progress.startStep('Copy Main Plugin Files');
    try {
      await createReleaseDirectory(spinner, MAIN_PLUGIN_NAME, MAIN_PLUGIN_PATH);
      progress.completeStep('Copy Main Plugin Files');
      spinner.succeed('Main plugin files copied and cleaned');
    } catch (error) {
      spinner.fail('Failed to copy main plugin files');
      throw error;
    }

    // Step 7: Copy plugin files and prepare release directory
    spinner = progress.startStep('Copy Pro Plugin Files');
    try {
      await createReleaseDirectory(spinner, PRO_PLUGIN_NAME, PRO_PLUGIN_PATH);
      progress.completeStep('Copy Pro Plugin Files');
      spinner.succeed('Pro plugin files copied and cleaned');
    } catch (error) {
      spinner.fail('Failed to copy pro plugin files');
      throw error;
    }

    // Step 8: Create main plugin zip file
    spinner = progress.startStep('Create Main Zip Package');
    let mainZipPath: string;
    try {
      mainZipPath = await createZipFile(
        mainVersion,
        finalOptions.outputDir,
        spinner,
        MAIN_PLUGIN_NAME,
      );
      progress.completeStep('Create Main Zip Package');
      spinner.succeed('Main zip package created');
    } catch (error) {
      spinner.fail('Failed to create main zip package');
      throw error;
    }

    // Step 9: Create pro plugin zip file
    spinner = progress.startStep('Create Pro Zip Package');
    let proZipPath: string;
    try {
      proZipPath = await createZipFile(
        proVersion,
        finalOptions.outputDir,
        spinner,
        PRO_PLUGIN_NAME,
      );
      progress.completeStep('Create Pro Zip Package');
      spinner.succeed('Pro zip package created');
    } catch (error) {
      spinner.fail('Failed to create pro zip package');
      throw error;
    }

    // Step 10: Package gateway plugins
    spinner = progress.startStep('Package Gateway Plugins');
    let gatewayPlugins: { pluginName: string; zipPath: string }[];
    try {
      gatewayPlugins = await packageAllGatewayPlugins(finalOptions.outputDir, spinner);
      progress.completeStep('Package Gateway Plugins');
      spinner.succeed('Gateway plugins packaged');
    } catch (error) {
      spinner.fail('Failed to package gateway plugins');
      throw error;
    }

    // Step 11: Generate release notes
    spinner = progress.startStep('Generate Release Notes');
    let releaseNotesPath: string;
    try {
      releaseNotesPath = generateReleaseNotes(mainVersion, finalOptions.outputDir, spinner);
      progress.completeStep('Generate Release Notes');
      spinner.succeed('Release notes generated');
    } catch (error) {
      spinner.fail('Failed to generate release notes');
      throw error;
    }

    // Step 12: Cleanup (unless skipped)
    if (!finalOptions.skipCleanup) {
      spinner = progress.startStep('Cleanup');
      try {
        cleanup(spinner);
        progress.completeStep('Cleanup');
        spinner.succeed('Cleanup completed');
      } catch (error) {
        spinner.fail('Cleanup failed');
        throw error;
      }
    } else {
      console.log('⏭️  Skipping cleanup (--skip-cleanup flag)');
    }

    console.log(`\n🎉 Plugin packaging completed successfully!`);
    console.log(`📊 ${progress.getProgress()}`);
    console.log(`📦 Main Package: ${mainZipPath}`);
    console.log(`📦 Pro Package: ${proZipPath}`);
    console.log(`📝 Release Notes: ${releaseNotesPath}`);

    if (gatewayPlugins.length > 0) {
      console.log(`\n🔌 Gateway Plugins Packaged:`);
      gatewayPlugins.forEach((gw) => {
        console.log(`   📦 ${gw.pluginName}: ${gw.zipPath}`);
      });
    }

    return { mainZipPath, proZipPath, releaseNotesPath, mainVersion, proVersion, gatewayPlugins };
  } catch (error) {
    console.log('\n❌ Plugin packaging failed:');
    console.error(error instanceof Error ? error.message : 'Unknown error');
    throw error;
  }
}

/**
 * Main function for CLI usage
 */
async function main(): Promise<void> {
  try {
    await packagePlugin();
  } catch (_error) {
    process.exit(1);
  }
}

// Run the script when called directly
if (import.meta.url === pathToFileURL(process.argv[1]).href) {
  if (process.argv.length === 3 && process.argv[2] === '--gateways') {
    await packageAllGatewayPlugins();
  } else {
    void main();
  }
}
