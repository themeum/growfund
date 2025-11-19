/* eslint-disable no-console */
import { existsSync, readdirSync, statSync } from 'fs';
import { join } from 'path';

import { ROOT_DIR } from '../config/paths.js';
import { execCommandAsync } from '../utils/executor.js';
import { ensureDirectoryExists } from '../utils/filesystem.js';
import type { LoadingSpinner } from '../utils/spinner.js';

/**
 * Interface for gateway plugin information
 */
export interface GatewayPlugin {
  name: string;
  path: string;
  hasManifest: boolean;
  hasLogo: boolean;
}

/**
 * Discover all growfund-gateway-* plugins in the gateway-server/gateways directory
 */
export function discoverGatewayPlugins(): GatewayPlugin[] {
  // Look in both the old location (wordpress/wp-content/plugins) and new location (gateway-server/gateways)
  const pluginsDir = join(ROOT_DIR, 'gateway-server/gateways');

  const gatewayPlugins: GatewayPlugin[] = [];

  if (!existsSync(pluginsDir)) {
    console.log(`⚠️  Gateway directory not found: ${pluginsDir} (skipping)`);
    return gatewayPlugins;
  }

  const items = readdirSync(pluginsDir);

  for (const item of items) {
    const itemPath = join(pluginsDir, item);

    // Check if it's a directory and starts with 'growfund-gateway-'
    if (statSync(itemPath).isDirectory() && item.startsWith('growfund-gateway-')) {
      const manifestPath = join(itemPath, 'manifest.json');
      const logoPath = join(itemPath, 'logo.png');

      // Avoid duplicates if gateway exists in both locations
      const existingPlugin = gatewayPlugins.find((plugin) => plugin.name === item);
      if (!existingPlugin) {
        gatewayPlugins.push({
          name: item,
          path: itemPath,
          hasManifest: existsSync(manifestPath),
          hasLogo: existsSync(logoPath),
        });
      }
    }
  }

  return gatewayPlugins;
}

/**
 * Package a single gateway plugin
 */
export async function packageGatewayPlugin(
  plugin: GatewayPlugin,
  outputDir: string,
  spinner?: LoadingSpinner,
): Promise<string> {
  if (spinner) {
    spinner.update(`Packaging ${plugin.name}...`);
  } else {
    console.log(`\n📦 Packaging gateway plugin: ${plugin.name}`);
  }

  const finalZipName = `${plugin.name}.zip`;
  const finalZipPath = join(outputDir, finalZipName);
  const preBuiltZipPath = join(plugin.path, finalZipName);

  const tempDir = join(outputDir, `temp-${plugin.name}`)
  const finalPluginTempDir = join(tempDir, plugin.name);
  ensureDirectoryExists(tempDir);
  ensureDirectoryExists(finalPluginTempDir);

  // Step 1: Copy all plugin files to the temp directory
  if (spinner) {
    spinner.update(`Copying files for ${plugin.name}...`);
  } else {
    console.log(`📁 Copying files for ${plugin.name}...`);
  }

  if (!plugin.hasManifest) {
    console.warn(`⚠️  Warning: manifest.json not found for ${plugin.name}`);
  }

  if (!plugin.hasLogo) {
    console.warn(`⚠️  Warning: logo.png not found for ${plugin.name}`);
  }

  // Copy everything from plugin.path to tempDir (preserving structure)
  console.log(`📁 Copying files from ${plugin.path}...`);
  await execCommandAsync(
    `rsync -av --exclude='*.zip' "${plugin.path}/" "${finalPluginTempDir}/"`,
    ROOT_DIR,
    spinner,
  );

  // Step 2: Run composer install inside tempDir
  if (existsSync(join(tempDir, 'composer.json'))) {
    if (spinner) {
      spinner.update(`Running composer install for ${plugin.name}...`);
    } else {
      console.log(`🎼 Running composer install for ${plugin.name}...`);
    }

    await execCommandAsync('composer install --no-dev --optimize-autoloader', tempDir, spinner);
  } else {
    if (!spinner) {
      console.log(`⚠️ No composer.json found for ${plugin.name}, skipping composer install`);
    }
  }

  // Step 3: Remove any .zip files inside tempDir
  if (spinner) {
    spinner.update(`Removing old zip files for ${plugin.name}...`);
  } else {
    console.log(`🧹 Removing old zip files for ${plugin.name}...`);
  }

  await execCommandAsync(`find . -name "*.zip" -type f -delete`, finalPluginTempDir, spinner);

  // Step 4: Create final zip file
  if (spinner) {
    spinner.update(`Creating final zip for ${plugin.name}...`);
  } else {
    console.log(`📦 Creating final zip for ${plugin.name}...`);
  }

  // Zip the entire contents of tempDir
  const zipCommand = `zip -r "${finalZipPath}" "${plugin.name}"`;
  await execCommandAsync(zipCommand, tempDir, spinner);

  // Step 5: Clean up temp directory
  if (spinner) {
    spinner.update(`Cleaning up temporary files for ${plugin.name}...`);
  } else {
    console.log(`🧽 Cleaning up temporary files for ${plugin.name}...`);
  }

  await execCommandAsync(`rm -rf "${tempDir}"`, ROOT_DIR, spinner);
  await execCommandAsync(`rm -rf "${preBuiltZipPath}"`, ROOT_DIR, spinner);

  // 📂 Step 6: Copy the built zip back to plugin path
  if (spinner) {
    spinner.update(`Copying ${plugin.name}.zip back to plugin directory...`);
  } else {
    console.log(`📂 Copying ${plugin.name}.zip back to plugin directory...`);
  }
  await execCommandAsync(`cp "${finalZipPath}" "${preBuiltZipPath}"`, ROOT_DIR, spinner);

  if (!spinner) {
    console.log(`✅ Gateway plugin packaged: ${finalZipPath}`);
  }

  return finalZipPath;
}

/**
 * Build gateway zips using the gateway-server build script
 */
export async function buildGatewayZips(spinner?: LoadingSpinner): Promise<void> {
  const gatewayServerDir = join(ROOT_DIR, 'gateway-server');

  if (!existsSync(gatewayServerDir)) {
    if (spinner) {
      spinner.update('Gateway server directory not found, skipping zip build...');
    } else {
      console.log('⚠️  Gateway server directory not found, skipping zip build');
    }
    return;
  }

  if (spinner) {
    spinner.update('Building gateway zip files...');
  } else {
    console.log('🔨 Building gateway zip files...');
  }

  try {
    // Run the gateway build script
    await execCommandAsync('npm run build:gateways', gatewayServerDir, spinner);

    if (!spinner) {
      console.log('✅ Gateway zip files built successfully');
    }
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
  } catch (error) {
    if (!spinner) {
      console.warn('⚠️  Failed to build gateway zips, will package from source');
    }
    // Don't throw error, just continue with manual packaging
  }
}

/**
 * Package all discovered gateway plugins
 */
export async function packageAllGatewayPlugins(
  outputDir?: string,
  spinner?: LoadingSpinner,
): Promise<{ pluginName: string; zipPath: string }[]> {
  const finalOutputDir = outputDir ?? ROOT_DIR;

  if (spinner) {
    spinner.update('Discovering gateway plugins...');
  } else {
    console.log('\n🔍 Discovering gateway plugins...');
  }

  const gatewayPlugins = discoverGatewayPlugins();

  if (gatewayPlugins.length === 0) {
    if (!spinner) {
      console.log('📭 No gateway plugins found');
    }
    return [];
  }

  if (!spinner) {
    console.log(`📋 Found ${gatewayPlugins.length} gateway plugin(s):`);
    gatewayPlugins.forEach((plugin) => {
      const isGatewayServer = plugin.path.includes('gateway-server/gateways');
      const preBuiltZip = existsSync(join(plugin.path, `${plugin.name}.zip`));
      const sourceInfo = isGatewayServer
        ? preBuiltZip
          ? '🏗️ (pre-built)'
          : '🔨 (source)'
        : '🔨 (source)';

      console.log(
        `   - ${plugin.name} ${sourceInfo} (manifest: ${plugin.hasManifest ? '✅' : '❌'}, logo: ${
          plugin.hasLogo ? '✅' : '❌'
        })`,
      );
    });
  }

  // Build gateway zips for gateway-server structure if needed
  const gatewayServerPlugins = gatewayPlugins.filter((plugin) =>
    plugin.path.includes('gateway-server/gateways'),
  );
  if (gatewayServerPlugins.length > 0) {
    await buildGatewayZips(spinner);
  }

  // Ensure output directory exists
  ensureDirectoryExists(finalOutputDir);

  // Package each gateway plugin
  const results: { pluginName: string; zipPath: string }[] = [];

  for (const plugin of gatewayPlugins) {
    try {
      const zipPath = await packageGatewayPlugin(plugin, finalOutputDir, spinner);
      results.push({ pluginName: plugin.name, zipPath });
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : 'Unknown error';
      console.error(`❌ Failed to package ${plugin.name}: ${errorMessage}`);
      throw error;
    }
  }

  if (!spinner) {
    console.log(`\n🎉 Successfully packaged ${results.length} gateway plugin(s)`);
    results.forEach((result) => {
      console.log(`   📦 ${result.pluginName}: ${result.zipPath}`);
    });
  }

  return results;
}
