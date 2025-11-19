/* eslint-disable no-console */
import { execSync, spawn } from 'child_process';

import type { LoadingSpinner } from './spinner.js';

/**
 * Execute a command synchronously with optional spinner integration
 */
export function execCommand(
  command: string,
  workingDir?: string,
  silent = false,
  spinner?: LoadingSpinner,
): string {
  try {
    if (silent) {
      const result = execSync(command, {
        cwd: workingDir ?? process.cwd(),
        stdio: 'pipe',
        encoding: 'utf8',
        env: { ...process.env },
      });
      return result.trim();
    } else {
      if (!spinner) {
        console.log(`🔄 Running: ${command}`);
        console.log(`📁 Working directory: ${workingDir ?? process.cwd()}`);
      }

      execSync(command, {
        cwd: workingDir ?? process.cwd(),
        stdio: spinner ? 'pipe' : 'inherit',
        env: { ...process.env },
      });

      return '';
    }
  } catch (error) {
    if (spinner) {
      spinner.fail(`Command failed: ${command}`);
    } else {
      console.error(`❌ Error running command: ${command}`);
    }
    console.error(`Error: ${error instanceof Error ? error.message : 'Unknown error'}`);
    throw error;
  }
}

/**
 * Execute a command asynchronously with spinner integration
 * This allows the spinner to animate while the command runs
 */
export async function execCommandAsync(
  command: string,
  workingDir?: string,
  spinner?: LoadingSpinner,
): Promise<string> {
  return new Promise((resolve, reject) => {
    if (!spinner) {
      console.log(`🔄 Running: ${command}`);
      console.log(`📁 Working directory: ${workingDir ?? process.cwd()}`);
    }

    // Use shell to execute command properly
    const child = spawn(command, {
      cwd: workingDir ?? process.cwd(),
      stdio: spinner ? 'pipe' : 'inherit',
      env: { ...process.env },
      shell: true,
    });

    let output = '';
    let errorOutput = '';

    if (child.stdout) {
      child.stdout.on('data', (data: Buffer) => {
        output += data.toString();
      });
    }

    if (child.stderr) {
      child.stderr.on('data', (data: Buffer) => {
        errorOutput += data.toString();
      });
    }

    child.on('close', (code) => {
      if (code === 0) {
        resolve(output.trim());
      } else {
        const error = new Error(`Command failed with exit code ${code}: ${errorOutput || output}`);
        if (spinner) {
          spinner.fail(`Command failed: ${command}`);
        } else {
          console.error(`❌ Error running command: ${command}`);
        }
        reject(error);
      }
    });

    child.on('error', (error) => {
      if (spinner) {
        spinner.fail(`Command failed: ${command}`);
      } else {
        console.error(`❌ Error running command: ${command}`);
      }
      reject(error);
    });
  });
}
