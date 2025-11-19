import { __ } from '@wordpress/i18n';
import React, { useCallback, useEffect, useId, useRef, useState } from 'react';

import ManageEditorShortCodes from '@/components/editor/manage-editor-shortcodes';
import { growfundConfig, wordpress } from '@/config/growfund';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { type EditorSettings, type TinyMCEEditor } from '@/types/editor';
import { isDefined } from '@/utils';

type TinymceEditorProps = Omit<
  EditorSettings['tinymce'],
  | 'setup'
  | 'plugins'
  | 'toolbar1'
  | 'toolbar2'
  | 'content_css'
  | 'placeholder'
  | 'body_class'
  | 'skin'
  | 'skin_url'
>;

export interface EditorSettingsProps extends Omit<EditorSettings, 'tinymce'> {
  tinymce?: TinymceEditorProps;
}

const getToolbar = (
  isMobile: boolean,
  isMinimal?: boolean,
  propsToolbar1?: string,
  propsToolbar2?: string,
): { toolbar1: string; toolbar2: string } => {
  // supported toolbar buttons:
  // 'formatselect fontselect fontsizeselect bold italic underline blockquote bullist numlist alignleft aligncenter alignright alignjustify wp_help shortcode_button wp_more wp_adv image media subscript superscript outdent indent strikethrough hr forecolor backcolor removeformat charmap link unlink undo redo'

  let toolbar1 =
    propsToolbar1 ??
    (isMinimal
      ? `formatselect | bold italic underline | image | shortcode_button`
      : `fontselect fontsizeselect | bold italic underline blockquote | alignleft aligncenter alignright alignjustify | bullist numlist | undo redo | shortcode_button wp_more wp_adv`);
  let toolbar2 =
    propsToolbar2 ??
    'subscript superscript | outdent indent | strikethrough hr | forecolor backcolor | removeformat charmap | link unlink ';

  if (isMobile) {
    toolbar1 = toolbar1.replace(/ \| /g, ' ');
    toolbar2 = toolbar2.replace(/ \| /g, ' ');
  }

  return {
    toolbar1,
    toolbar2,
  };
};

const Editor = React.forwardRef<
  HTMLTextAreaElement,
  React.ComponentProps<'textarea'> & {
    value: string | null | undefined;
    onChange: (value: string) => void;
    settings?: EditorSettingsProps;
    shortCodes?: { label: string; value: string }[];
    isMinimal?: boolean;
    toolbar1?: string;
    toolbar2?: string;
  }
>(
  (
    {
      className,
      value,
      onChange,
      placeholder,
      shortCodes,
      toolbar1,
      toolbar2,
      settings = {},
      isMinimal,
      ...props
    },
    ref,
  ) => {
    const isMobile = useIsMobile();
    const editorRef = useRef<TinyMCEEditor>(null);
    const containerId = useId();
    const editorId = useId();
    const [isEditorReady, setIsEditorReady] = useState(false);
    const [isInitializing, setIsInitializing] = useState(false);
    const [isActiveVisualTab, setIsActiveVisualTab] = useState(isMinimal ?? false);
    const [openShortcodes, setOpenShortcodes] = useState(false);
    const isShortcodeLoaded = useRef(false);
    const hasQuickTagShortcode = useRef(false);
    const shortcodePopoverRef = useRef<HTMLDivElement | null>(null);
    const textareaRef = useRef<HTMLTextAreaElement | null>(null);

    const toolbar = getToolbar(isMobile, isMinimal, toolbar1, toolbar2);

    const defaultSettings: EditorSettings = {
      tinymce: {
        wpautop: false,
        plugins:
          'charmap,colorpicker,hr,fullscreen,lists,image,media,paste,tabfocus,textcolor,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview',
        toolbar: true,
        toolbar1: toolbar.toolbar1,
        toolbar2: isMinimal ? false : toolbar.toolbar2,
        setup: (editor: TinyMCEEditor) => {
          if (isDefined(shortCodes)) {
            const isDisable = !isDefined(shortCodes) || shortCodes.length === 0;
            editor.addButton('shortcode_button', {
              text: __('Shortcodes', 'growfund'),
              classes: 'shortcode-button-class',
              title: !isDisable
                ? __('Shortcodes', 'growfund')
                : __('No shortcodes available', 'growfund'),
              onclick: () => {
                setOpenShortcodes(true);
              },
              disabled: isDisable,
            });
          }
        },
        content_css: `${growfundConfig.site_url}/wp-includes/css/dashicons.min.css,${growfundConfig.site_url}/wp-includes/js/tinymce/skins/wordpress/wp-content.css,${growfundConfig.site_url}/wp-content/plugins/growfund/resources/assets/lib/tinymce/light/content.css`,
        placeholder: placeholder ?? __('Type here...', 'growfund'),
        body_class: 'wp-editor',
        resize: 'vertical',
        menubar: false,
        wpautoresize_on_init: true,
        add_unload_trigger: false,
        remove_linebreaks: false,
        convert_newlines_to_brs: false,
        remove_redundant_brs: false,
        autoresize_min_height: 200,
        autoresize_max_height: 500,
        wp_autoresize_on: true,
        browser_spellcheck: false,
        convert_urls: false,
        end_container_on_empty_block: true,
        entities: '38,amp,60,lt,62,gt',
        entity_encoding: 'raw',
        fix_list_elements: true,
        indent: false,
        relative_urls: 0,
        remove_script_host: 0,
        submit_patch: true,
        link_context_toolbar: false,
        theme: 'modern',
        statusbar: true,
        branding: false,
        wp_keep_scroll_position: false,
        wpeditimage_html5_captions: true,
        ...settings.tinymce,
      },
      quicktags:
        typeof settings.quicktags === 'boolean'
          ? settings.quicktags
          : isMinimal
          ? false
          : {
              buttons: 'strong,em,link,block,del,ins,img,ul,ol,li,code,more,close',
              ...settings.quicktags,
            },
      mediaButtons: settings.mediaButtons !== false && !isMinimal,
    };

    const initializeEditor = useCallback(() => {
      if (typeof wordpress.editor === 'undefined' || typeof window.tinymce === 'undefined') {
        setTimeout(initializeEditor, 100);
        return;
      }

      setIsInitializing(true);

      try {
        if (wordpress.editor.getDefaultSettings) {
          wordpress.editor.remove(editorId);
        }
        if (window.tinymce.get(editorId)) {
          window.tinymce.remove(editorId);
        }
      } catch (error) {
        console.warn('Error removing existing editor', error);
      }

      try {
        wordpress.editor.initialize(editorId, defaultSettings);
      } catch (error) {
        console.error('Error initializing wordpress editor', error);
        setIsInitializing(false);
        return;
      }

      const checkTinyMCE = () => {
        const editor = window.tinymce.get(editorId);
        if (editor) {
          editorRef.current = editor;
          setIsEditorReady(true);

          if (value) {
            editor.setContent(value);
          }

          editor.on('change keyup paste', () => {
            try {
              const newContent = editor.getContent();
              onChange(newContent);
            } catch (error) {
              console.warn('Error getting editor content', error);
            }
          });

          editor.on('wp-autoresize', () => {
            try {
              onChange(editor.getContent());
            } catch (error) {
              console.warn('Error getting editor content', error);
            }
          });
        } else {
          setTimeout(checkTinyMCE, 100);
        }
      };
      setTimeout(checkTinyMCE, 100);
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [editorId]);

    const initializeShortcodeButtonInQuickTags = useCallback(() => {
      if (
        !isDefined(window.QTags) ||
        hasQuickTagShortcode.current ||
        !isEditorReady ||
        !isDefined(shortCodes) ||
        shortCodes.length === 0
      )
        return;

      const instance = window.QTags.getInstance(editorId);

      if (instance) {
        const buttonId = 'shortcode_qt_button';
        window.QTags.addButton(
          buttonId,
          __('Shortcodes', 'growfund'),
          () => {
            setOpenShortcodes(true);
          },
          '',
          '',
          __('Shortcodes', 'growfund'),
          0, // set in last position of the list
          editorId,
        );

        window.QTags._buttonsInit(editorId);

        hasQuickTagShortcode.current = true;
      }
    }, [editorId, isEditorReady, shortCodes]);

    const getTextarea = useCallback(() => {
      const textarea = (textareaRef.current ??
        document.getElementById(editorId)) as HTMLTextAreaElement | null;
      return textarea;
    }, [editorId]);

    const setTextareaValue = useCallback(
      (textarea: HTMLTextAreaElement, insertingString: string) => {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;
        const newValue = text.slice(0, start) + insertingString + text.slice(end);
        textarea.value = newValue;
        onChange(newValue);
        textarea.selectionStart = textarea.selectionEnd = start + insertingString.length + 1;
      },
      [onChange],
    );

    // Initialize the editor
    useEffect(() => {
      if (isInitializing) {
        return;
      }

      initializeEditor();

      return () => {
        if (typeof wordpress.editor !== 'undefined') {
          wordpress.editor.remove(editorId);
        }
        if (editorRef.current) {
          editorRef.current.remove(editorId);
        }
        setIsInitializing(false);
        setIsEditorReady(false);
      };
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [editorId]);

    // Re initialize the editor if shortcodes loaded dependencies change
    useEffect(() => {
      if (isShortcodeLoaded.current || !shortCodes || shortCodes.length === 0) return;

      isShortcodeLoaded.current = true;
      initializeEditor();
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [shortCodes]);

    // check if the visual tab is active
    useEffect(() => {
      if (isMinimal) {
        return;
      }

      const wrapper = document.querySelector(`#${containerId}`);

      if (!wrapper) return;

      const updateTabState = () => {
        const visualTab = wrapper.querySelector('.tmce-active');
        setIsActiveVisualTab(!!visualTab);
      };

      // Run once at start
      updateTabState();

      const observer = new MutationObserver(() => {
        updateTabState();
      });

      observer.observe(wrapper, {
        attributes: true,
        subtree: true,
        attributeFilter: ['class'],
        childList: true,
      });

      return () => {
        observer.disconnect();
      };
    }, [containerId, isMinimal]);

    // show toolbar of the editor if visual tab is active
    useEffect(() => {
      if (isEditorReady && isActiveVisualTab) {
        const topPart = document.querySelector(
          `#${containerId} .wp-editor-container .mce-top-part`,
        );
        if (topPart) {
          topPart.classList.add('in');
        }
      }
    }, [isActiveVisualTab, isEditorReady, containerId]);

    // set shortcode button position
    useEffect(() => {
      if (isEditorReady && isActiveVisualTab) {
        const shortcodeButton = document.querySelector(
          `#${containerId} .wp-editor-container .mce-shortcode-button-class`,
        );
        if (shortcodeButton && shortcodePopoverRef.current) {
          const rect = shortcodeButton.getBoundingClientRect();
          shortcodePopoverRef.current.style.position = 'absolute';
          shortcodePopoverRef.current.style.top = `${rect.top + window.scrollY}px`;
          shortcodePopoverRef.current.style.left = `${rect.left + window.scrollX}px`;
          shortcodePopoverRef.current.style.width = '1px';
          shortcodePopoverRef.current.style.height = '1px';
        }
      }
    }, [shortcodePopoverRef, isActiveVisualTab, isEditorReady, containerId]);

    // Initialize the shortcode button in quick tags
    useEffect(() => {
      if (!isEditorReady) return;

      const textarea = document.getElementById(editorId) as HTMLTextAreaElement | null;
      if (!textarea) return;

      textareaRef.current = textarea;

      initializeShortcodeButtonInQuickTags();
    }, [editorId, isEditorReady, initializeShortcodeButtonInQuickTags]);

    // handle text area focus
    useEffect(() => {
      if (!isEditorReady) return;

      const textarea = getTextarea();
      if (!textarea) return;

      const handleFocus = () => {
        if (openShortcodes) {
          setOpenShortcodes(false);
        }
      };

      textarea.addEventListener('focus', handleFocus);

      return () => {
        textarea.removeEventListener('focus', handleFocus);
      };
    }, [editorId, isEditorReady, openShortcodes, getTextarea]);

    // update editor value
    useEffect(() => {
      if (isEditorReady && editorRef.current && value !== editorRef.current.getContent()) {
        editorRef.current.setContent(value ?? '');
      }
    }, [value, isEditorReady, containerId]);

    // handle editor focus if the visual tab is active
    useEffect(() => {
      const editor = window.tinymce.get(editorId);
      if (editor) {
        editor.on('focus', () => {
          if (openShortcodes) {
            setOpenShortcodes(false);
          }
        });
      }
    }, [editorId, openShortcodes]);

    return (
      <>
        <ManageEditorShortCodes
          popoverRef={shortcodePopoverRef}
          open={openShortcodes}
          onOpenChange={(open) => {
            setOpenShortcodes(open);
            if (open) return;

            if (!isActiveVisualTab) {
              const textarea = getTextarea();
              if (textarea) {
                textarea.focus();
              }
              return;
            }

            if (editorRef.current) {
              editorRef.current.focus();
            }
          }}
          allShortcodes={shortCodes ?? []}
          onSelect={(shortcode) => {
            setOpenShortcodes(false);
            if (isActiveVisualTab) {
              editorRef.current?.insertContent(shortcode);
              return;
            }

            const textarea = getTextarea();
            if (!textarea) return;
            setTextareaValue(textarea, shortcode);
          }}
          popoverContainer={document.querySelector(
            `#${containerId} .wp-editor-container .mce-shortcode-button-class`,
          )}
        />
        <div id={containerId} className={cn('growfund-wp-editor', className)}>
          <textarea
            {...props}
            ref={ref}
            id={editorId}
            name={editorId}
            className="growfund-editor-area"
            value={value ?? ''}
            onChange={(event) => {
              onChange(event.target.value);
            }}
            style={{
              width: '100%',
              minHeight: props.rows ? `${props.rows * 20}px` : '200px',
              fontFamily: 'Inter, sans-serif',
              fontSize: '13px',
            }}
          />
        </div>
      </>
    );
  },
);

Editor.displayName = 'Editor';

export { Editor };
