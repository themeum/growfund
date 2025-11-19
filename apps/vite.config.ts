import react from '@vitejs/plugin-react';
import path from 'path';
import type { PreRenderedChunk } from 'rollup';
import { visualizer } from 'rollup-plugin-visualizer';
import { defineConfig, type BuildOptions } from 'vite';
import svgr from 'vite-plugin-svgr';

const pluginPath = path.resolve(__dirname, '../wordpress/wp-content/plugins');
const growfundPath = path.resolve(pluginPath, './growfund');
const growfundProPath = path.resolve(pluginPath, './growfund-pro');

const getSharedManualChunks = () => ({
  // Split React libraries for better caching - consistent across builds
  react: ['react'],
  'react-dom': ['react-dom'],
  'react-dom-client': ['react-dom/client'],
  'react-jsx-runtime': ['react/jsx-runtime'],

  // Router and state management
  router: ['react-router', '@tanstack/react-query'],

  // UI component libraries
  'ui-radix': [
    '@radix-ui/react-dialog',
    '@radix-ui/react-dropdown-menu',
    '@radix-ui/react-popover',
    '@radix-ui/react-select',
    '@radix-ui/react-tabs',
    '@radix-ui/react-tooltip',
    '@radix-ui/react-switch',
    '@radix-ui/react-checkbox',
    '@radix-ui/react-radio-group',
  ],

  // Form handling
  forms: ['react-hook-form', '@hookform/resolvers', 'zod'],

  // Charts and visualization
  charts: ['recharts'],

  // PDF generation - chunk @react-pdf separately for better caching
  'pdf-core': ['@react-pdf/renderer'],
  'pdf-html': ['react-pdf-html'],

  // Icons
  icons: ['lucide-react', '@radix-ui/react-icons'],

  // Date utilities
  'date-utils': ['date-fns', 'react-day-picker'],

  // Utilities
  utils: ['axios', 'clsx', 'tailwind-merge', 'class-variance-authority', 'tinycolor2'],

  // WordPress specific
  wordpress: ['@wordpress/i18n'],

  // Drag and drop
  'dnd-kit': ['@dnd-kit/core', '@dnd-kit/sortable', '@dnd-kit/utilities', '@dnd-kit/modifiers'],

  ...(process.env.NODE_ENV === 'development' && {
    'dev-tools': ['swagger-ui-react', 'react-scan'],
  }),
});

const createBuildConfig = (appType: 'main' | 'pro'): BuildOptions => {
  const isMainApp = appType === 'main';
  const outputDir = isMainApp
    ? path.resolve(growfundPath, './resources/dist')
    : path.resolve(growfundProPath, './resources/dist');

  const rollupOptions = {
    input: 'growfund/src/main.tsx',
    treeshake: {
      moduleSideEffects: false,
      propertyReadSideEffects: false,
      tryCatchDeoptimization: false,
    },
    output: {
      entryFileNames: '[name].[hash].js',
      chunkFileNames: (chunkInfo: PreRenderedChunk) => {
        const facadeModuleId = chunkInfo.facadeModuleId
          ? chunkInfo.facadeModuleId.split('/').pop()
          : 'chunk';
        if (['react', 'react-dom'].includes(chunkInfo.name)) {
          return `[name].js`;
        }
        return `[name].${facadeModuleId}.[hash].js`;
      },
      assetFileNames: 'assets/[name].[hash].[ext]',
      format: 'es' as const,
      // Use consistent chunking for both builds to avoid React conflicts
      manualChunks: getSharedManualChunks(),
    },
  };

  // For main build, externalize `/growfund-pro/src/index.ts`
  if (isMainApp) {
    (rollupOptions as any).external = [
      /^@growfund\/pro(\/.*)?$/,
      path.resolve(__dirname, './growfund-pro/src/index.ts'),
    ];
  }

  return {
    outDir: outputDir,
    emptyOutDir: true,
    sourcemap: process.env.NODE_ENV === 'development',
    assetsDir: 'assets',
    target: 'es2020',
    minify: 'terser' as const,
    terserOptions: {
      format: {
        comments: (_node, comment) => {
          const text = comment.value.trim();
          return text.startsWith('translators:');
        },
      },
      mangle: {
        reserved: ['__', '_n', '_x', '_nx', 'sprintf'],
      },
    },
    cssMinify: true,
    cssCodeSplit: true,
    manifest: true,
    rollupOptions: {
      ...rollupOptions,
      preserveEntrySignatures: 'strict' as const,
    },
  };
};

// Determine which app to build based on environment variable
const appType = (process.env.VITE_APP_TYPE as 'main' | 'pro') || 'main';

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    svgr(),
    process.env.ANALYZE === 'true' &&
      visualizer({
        filename: 'bundle-analysis.html',
        open: true,
        gzipSize: true,
        brotliSize: true,
      }),
  ].filter(Boolean),
  base: '',
  define: {
    __SCAN_ENABLED__: JSON.stringify(process.env.VITE_SCAN || 'false'),
    __PLUGIN_URL__: JSON.stringify(
      appType === 'main' ? '/wp-content/plugins/growfund' : '/wp-content/plugins/growfund-pro',
    ),
    __ENV_MODE__: JSON.stringify(process.env.NODE_ENV || 'development'),
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'development'),
    'process.env.GENERATE_SOURCEMAP': JSON.stringify(process.env.NODE_ENV !== 'production'),
    'process.env.VITE_APP_TYPE': JSON.stringify(appType),
    __VERSION__: JSON.stringify('1.0.0'),
  },
  publicDir: 'growfund/public',
  build: createBuildConfig(appType),
  server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './growfund/src'),
      '@growfund/pro': path.resolve(__dirname, './growfund-pro/src'),
      '@wordpress/i18n': path.resolve(__dirname, './growfund/src/lib/i18n.ts'),
    },
  },
  optimizeDeps: {
    include: [
      'react',
      'react-dom',
      'react-router',
      '@tanstack/react-query',
      'react-hook-form',
      '@wordpress/i18n',
    ],
    exclude: [
      // Exclude large dev dependencies
      ...(process.env.NODE_ENV === 'production' ? ['swagger-ui-react'] : []),
    ],
  },
});
