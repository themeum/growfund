/// <reference types="vite/client" />
/// <reference types="vite-plugin-svgr/client" />

// Define global variables injected by Vite
declare const __SCAN_ENABLED__: string;
declare const __PLUGIN_URL__: string;
declare const __ENV_MODE__: 'development' | 'production';
declare global {
  interface Window {
    growfund: {
      site_url: string;
      rest_url_base: string;
      ajax_url: string;
      nonce: string;
      ajax_nonce: string;
      user_role: string;
      is_onboarding_completed: number;
      is_woocommerce_installed: number;
      version: string;
      debug: boolean;
      mode: string;
      assets_url: string;
    };
  }
}
