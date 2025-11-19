import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef, useState } from 'react';

import { wordpress } from '@/config/growfund';
import { type MediaAttachment } from '@/schemas/media';
import { type AcceptedMediaTypes } from '@/types/media';
import { isDefined } from '@/utils';

interface OpenMediaOptions {
  multiple?: boolean;
  title?: string;
  button_text?: string;
  types?: AcceptedMediaTypes[];
  onSelect?: (attachments: MediaAttachment[]) => void;
}

const defaultOptions: OpenMediaOptions = {
  multiple: false,
  title: __('Select or Upload Image', 'growfund'),
  button_text: __('Upload', 'growfund'),
};

function prepareAttachments(attachments: MediaAttachment[]) {
  return attachments.map<MediaAttachment>((media) => ({
    id: String(media.id),
    url: media.url,
    filename: media.filename,
    sizes: media.sizes,
    width: media.width,
    height: media.height,
    filesize: media.filesize,
    mime: media.mime,
    type: media.type,
    author: String(media.author),
    authorName: media.authorName,
    date: String(media.date),
    thumb: media.thumb,
  }));
}

const useWordpressMedia = () => {
  const [attachments, setAttachments] = useState<MediaAttachment[]>([]);
  const media = useRef<ReturnType<typeof wordpress.media> | null>(null);
  const [isMediaOpen, setIsMediaOpen] = useState(false);

  useEffect(() => {
    return () => {
      if (media.current) {
        media.current.off('select');
        media.current.off('open');
        media.current.off('close');
        media.current = null;
      }
    };
  }, []);

  useEffect(() => {
    window.dispatchEvent(new CustomEvent('wp-media-open', { detail: { isOpen: isMediaOpen } }));
    return () => {
      window.dispatchEvent(new CustomEvent('wp-media-open', { detail: { isOpen: false } }));
    };
  }, [isMediaOpen]);

  const openMediaModal = useCallback((options?: OpenMediaOptions) => {
    const { multiple, title, button_text, onSelect, types } = options ?? defaultOptions;

    media.current = wordpress.media({
      title,
      button: {
        text: button_text,
      },
      multiple,
      library: {
        ...(types && { type: types }),
      },
    });

    media.current.on('open', () => {
      setIsMediaOpen(true);
      const wpWrap = document.getElementById('wpwrap');

      if (wpWrap?.hasAttribute('aria-hidden')) {
        wpWrap.removeAttribute('aria-hidden');
      }
    });

    media.current.on('close', () => {
      setIsMediaOpen(false);
    });

    const handleClose = () => {
      media.current?.off('select');
      setIsMediaOpen(false);
    };

    media.current.on('select', () => {
      const selectionState = media.current?.state().get('selection');

      if (!selectionState) {
        return;
      }

      const attachments = multiple
        ? selectionState.map((item) => item.toJSON())
        : [selectionState.first().toJSON()];

      setAttachments(prepareAttachments(attachments));
      onSelect?.(prepareAttachments(attachments));
      handleClose();
    });

    media.current.open();
  }, []);

  return {
    openMediaModal,
    attachments,
    isMediaOpen,
  };
};

const useDialogCloseMiddleware = () => {
  const [isOpen, setIsOpen] = useState(false);

  useEffect(() => {
    const handler = (event: Event) => {
      const detail = (event as CustomEvent<{ isOpen: boolean }>).detail;
      if (!isDefined(detail) || !isDefined(detail.isOpen)) {
        return;
      }
      setIsOpen(detail.isOpen);
    };

    window.addEventListener('wp-media-open', handler);

    return () => {
      window.removeEventListener('wp-media-open', handler);
    };
  }, []);

  const applyMiddleware = useCallback(
    (callback: (status: boolean) => void) => {
      return (status: boolean) => {
        if (!status && isOpen) {
          return;
        }
        callback(status);
      };
    },
    [isOpen],
  );

  return { applyMiddleware };
};

export { useDialogCloseMiddleware, useWordpressMedia };
