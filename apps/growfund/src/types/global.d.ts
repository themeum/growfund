import { type TinyMCE, type TinyMCEEditor } from '@/types/editor';
import { type MediaArgs, type MediaFrame } from '@/types/media';

type MediaFunction = ((args: MediaArgs) => MediaFrame) & {
  view?: {
    settings?: {
      post?: { id?: number };
    };
  };
};

declare global {
  interface Window {
    growfund: {
      site_url: string;
      rest_url_base: string;
      ajax_url: string;
      nonce: string;
      ajax_nonce: string;
      user_role: 'administrator' | 'fundraiser' | 'donor' | 'backer';
      is_onboarding_completed: boolean;
      is_woocommerce_installed: boolean;
      is_donation_mode: boolean;
      version: string;
      debug: boolean;
      mode: string;
      assets_url: string;
      as_guest: boolean;
      is_pro: boolean;
      features: Record<string, boolean | { limit: number; is_pro: boolean }>;
      is_migration_available_from_crowdfunding: boolean;
    };
    wp: {
      media: MediaFunction;
      editor: TinyMCEEditor;
      i18n?: {
        hasTranslations?: boolean;
        __: (text: string) => string;
        _x: (text: string, context: string) => string;
        _n: (text: string, count: number, plural: string) => string;
        _nx: (text: string, plural: string, count: number, context: string) => string;
        sprintf: (text: string, ...args: unknown[]) => string;
        setLocaleData: (data: Record<string, string>, domain: string) => void;
        getLocaleData: (domain: string) => Record<string, string> | undefined;
      };
    };
    tinymce: TinyMCE;
    QTags: {
      addButton: (
        id: string,
        display: string,
        arg1: (() => void) | string,
        arg2?: string,
        access?: string,
        title?: string,
        priority?: 0 | 1, // 0 for prepend, 1 for append
        editorId?: string,
      ) => void;
      getInstance: (editorId: string) => {
        canvas: HTMLTextAreaElement;
        init: () => void;
        remove: () => void;
        id: string;
        settings: {
          buttons: string;
          id: string;
        };
        toolbar: HTMLElement;
      } | null;
      _buttonsInit: (editorId: string) => void;
    };
  }
}
