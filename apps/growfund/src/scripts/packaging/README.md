# Plugin Packaging System

This directory contains a well-organized, modular packaging system for the Growfund WordPress plugin.

## 📁 Directory Structure

```
packaging/
├── README.md                    # This file
├── index.ts                     # Main orchestrator
├── config/
│   └── paths.ts                 # Path constants and configuration
├── types/
│   └── options.ts               # TypeScript interfaces
├── utils/
│   ├── args.ts                  # Command line argument parser
│   ├── executor.ts              # Command execution utilities
│   ├── filesystem.ts            # File system operations
│   ├── spinner.ts               # Loading animations and progress tracking
│   └── validation.ts            # Environment validation
└── steps/
    ├── cleanup.ts               # Cleanup operations
    ├── dependencies.ts          # Node.js and PHP dependency management
    ├── package.ts               # Zip file creation
    ├── prepare.ts               # Release directory preparation
    └── release-notes.ts         # Release notes generation
```

## 🎯 Features

### ✨ Animated Loading Spinners

- **Smooth animations**: Unicode Braille patterns (`⠋ ⠙ ⠹ ⠸ ⠼ ⠴ ⠦ ⠧ ⠇ ⠏`)
- **Progress tracking**: Shows `[1/7]`, `[2/7]`, etc.
- **Dynamic messages**: Updates during multi-part operations
- **Success/failure indicators**: Clear ✅ or ❌ for each step

### 🏗️ Modular Architecture

- **Separation of concerns**: Each module has a single responsibility
- **Easy to test**: Individual functions can be unit tested
- **Maintainable**: Changes to one step don't affect others
- **Reusable**: Components can be used in other scripts

### 🔧 Robust Error Handling

- **Validation**: Checks environment before starting
- **Graceful failures**: Clear error messages with context
- **Cleanup**: Removes temporary files even on failure

### ⚡ Performance Optimized

- **Async operations**: Long-running commands don't block UI
- **Parallel execution**: Where possible, operations run concurrently
- **Minimal dependencies**: Uses Node.js built-ins where possible

## 🚀 Usage

### CLI Usage

```bash
# Basic usage
yarn build:package

# With options
tsx src/scripts/make-package.ts --version 1.2.3 --output-dir ./releases

# Skip build step (if already built)
tsx src/scripts/make-package.ts --skip-build

# Skip cleanup (for debugging)
tsx src/scripts/make-package.ts --skip-cleanup
```

### Programmatic Usage

```typescript
import { packagePlugin } from './packaging/index.js';

// Basic usage
const result = await packagePlugin();

// With options
const result = await packagePlugin({
  version: '1.2.3',
  outputDir: './releases',
  skipBuild: false,
  skipCleanup: false,
});

console.log(result.zipPath); // Path to created zip file
console.log(result.releaseNotesPath); // Path to release notes
console.log(result.version); // Version used
```

## 🔄 Process Flow

1. **Validate Environment** - Check required tools and directories
2. **Install Node.js Dependencies** - `yarn install --frozen-lockfile`
3. **Build TypeScript Application** - `yarn build` (optional)
4. **Install PHP Dependencies** - `composer install --no-dev`
5. **Copy Plugin Files** - Copy and clean development files
6. **Create Zip Package** - Compress into distributable zip
7. **Package Gateway Plugins** - Automatically discover and package payment gateways
8. **Generate Release Notes** - Create changelog and instructions
9. **Cleanup** - Remove temporary files (optional)

### 🔌 Gateway Plugin Discovery

The packaging system automatically discovers and packages payment gateway plugins from multiple locations:

- **Gateway Server**: `gateway-server/gateways/` (preferred, uses pre-built zips)
- **WordPress Plugins**: `wordpress/wp-content/plugins/` (fallback, builds from source)

**Smart Packaging Strategy:**

- ✅ **Pre-built ZIPs**: For `gateway-server/gateways/`, uses existing `.zip` files
- 🔨 **Source Building**: For WordPress plugins or missing zips, builds from source
- 🚀 **Auto-building**: Runs `npm run build:gateways` if pre-built zips are missing

## 🛠️ Configuration

### Path Configuration (`config/paths.ts`)

- `PLUGIN_NAME`: Plugin identifier
- `ROOT_DIR`: Project root directory
- `PLUGIN_PATH`: Plugin source directory
- `RELEASE_DIR`: Temporary release directory
- `TS_DIR`: TypeScript source directory

### Files Removed During Packaging

- Development files: `composer.json`, `phpcs.xml`, etc.
- TypeScript sources: `resources/ts/`
- Development vendor packages: `wp-coding-standards`, etc.

## 🎨 Spinner System

The spinner system provides visual feedback during long operations:

```typescript
import { LoadingSpinner, ProgressTracker } from './utils/spinner.js';

// Simple spinner
const spinner = new LoadingSpinner('Processing...');
spinner.start();
spinner.update('Still processing...');
spinner.succeed('Done!');

// Progress tracker
const progress = new ProgressTracker();
progress.addStep('Step 1');
progress.addStep('Step 2');

const stepSpinner = progress.startStep('Step 1');
// ... do work ...
progress.completeStep('Step 1');
stepSpinner.succeed('Step 1 completed');
```

## 🧪 Testing

Each module can be tested independently:

```typescript
import { validateEnvironment } from './utils/validation.js';
import { installNodeDependencies } from './steps/dependencies.js';

// Test validation
expect(() => validateEnvironment()).not.toThrow();

// Test with spinner
const spinner = new LoadingSpinner('Testing...');
await installNodeDependencies(spinner);
```

## 📝 Adding New Steps

To add a new packaging step:

1. Create a new file in `steps/`
2. Export async functions that accept optional `spinner` parameter
3. Add the step to the main orchestrator in `index.ts`
4. Update the progress tracker to include the new step

Example:

```typescript
// steps/my-new-step.ts
export async function myNewStep(spinner?: LoadingSpinner): Promise<void> {
  if (spinner) {
    spinner.update('Doing new step...');
  }
  // ... implementation ...
}

// index.ts
progress.addStep('My New Step');
spinner = progress.startStep('My New Step');
await myNewStep(spinner);
progress.completeStep('My New Step');
spinner.succeed('New step completed');
```

## 🐛 Troubleshooting

### Common Issues

1. **Command not found**: Ensure `yarn` and `composer` are installed
2. **Permission denied**: Check file permissions in target directories
3. **Path not found**: Verify the script is run from the correct directory
4. **Build failures**: Check TypeScript compilation errors first

### Debug Mode

Use `--skip-cleanup` to inspect temporary files:

```bash
tsx src/scripts/make-package.ts --skip-cleanup
ls -la release/  # Inspect release directory
```

### Verbose Output

For debugging without spinners, import individual functions and call them directly without the spinner parameter.

## 🤝 Contributing

When modifying the packaging system:

1. **Keep modules focused**: Each should have a single responsibility
2. **Update spinners**: Ensure all long operations show progress
3. **Handle errors gracefully**: Provide helpful error messages
4. **Update documentation**: Keep this README current
5. **Test thoroughly**: Verify all options and error conditions
