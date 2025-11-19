import { type EditorEvent } from 'tinymce';

/* eslint-disable @typescript-eslint/no-explicit-any */
interface TinyMCEEditor {
  setContent: (content: string) => void;
  getContent: () => string;
  insertContent: (content: string) => void;
  on: <T>(event: string, callback: (event: EditorEvent<T>) => void) => void;
  remove: (editorId: string) => void;
  focus: () => void;
  selection: {
    getContent: () => string;
    setContent: (content: string) => void;
  };
  getDefaultSettings?: () => TinyMCESettings;
  initialize: (editorId: string, settings: EditorSettings) => void;
  addIcon: (name: string, svg: string) => void;
  addButton: (
    id: string,
    spec: {
      text?: string;
      classes?: string;
      title?: string;
      tooltip?: string;
      icon?: boolean | string;
      type?: 'button' | 'menubutton';
      disabled?: boolean;
      onclick?: () => void;
      menu?: {
        text: string;
        onclick: () => void;
      }[];
    },
  ) => void;

  windowManager: {
    open: (settings: {
      title: string;
      body: (
        | {
            type: 'textbox';
            name: string;
            label: string;
            value?: string;
            tooltip?: string;
          }
        | {
            type: 'listbox';
            name: string;
            label: string;
            onselect?: () => void;
            values: { text: string; value: string }[];
          }
      )[];
      buttons?: {
        text: string;
        onclick: string | ((e: any) => void);
        subtype?: 'primary';
      }[];
      onsubmit?: (e: { data: Record<string, string> }) => void;
    }) => void;
  };
  notificationManager: {
    open: (options: { text: string; type: 'info' | 'error' | 'warning' | 'success' }) => void;
  };
}

interface TinyMCE {
  get: (editorId: string) => TinyMCEEditor | null;
  remove: (editorId: string) => void;
}

interface TinyMCESettings {
  wpautop?: boolean;
  plugins?: string;
  toolbar?: boolean;
  toolbar1?: string;
  toolbar2?: string | boolean;
  skin?: string;
  skin_url?: string;
  content_css?: string;
  body_class?: string;
  resize?: string | boolean;
  menubar?: boolean;
  wpautoresize_on_init?: boolean;
  add_unload_trigger?: boolean;
  remove_linebreaks?: boolean;
  convert_newlines_to_brs?: boolean;
  remove_redundant_brs?: boolean;
  height?: number;
  setup?: (editor: TinyMCEEditor) => void;
  placeholder?: string;
  autoresize_min_height?: number;
  autoresize_max_height?: number;
  wp_autoresize_on?: boolean;
  browser_spellcheck?: boolean;
  convert_urls?: boolean;
  end_container_on_empty_block?: boolean;
  entities?: string;
  entity_encoding?: string;
  fix_list_elements?: boolean;
  indent?: boolean;
  relative_urls?: number;
  remove_script_host?: number;
  submit_patch?: boolean;
  link_context_toolbar?: boolean;
  theme: 'modern' | 'inlite';
  statusbar?: boolean;
  branding?: boolean;
  [key: string]: any;
}

interface QuickTagsSettings {
  buttons?: string;
  [key: string]: any;
}

interface EditorSettings {
  tinymce?: TinyMCESettings;
  quicktags?: boolean | QuickTagsSettings;
  mediaButtons?: boolean;
}

export type { EditorSettings, QuickTagsSettings, TinyMCE, TinyMCEEditor, TinyMCESettings };
