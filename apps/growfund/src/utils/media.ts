import { type MediaAttachment, type VideoAttachment } from '@/schemas/media';

const byte2mb = (bytes: number) => {
  return bytes / (1024 * 1024);
};

const mb2byte = (mb: number) => {
  return mb * 1024 * 1024;
};

const getFileSize = (file: File | Blob) => {
  return file.size;
};

const getFileSizeInMB = (file: File | Blob) => {
  return byte2mb(getFileSize(file));
};

const createFileSizeValidator = (maxSize: number) => {
  return (file: File) => {
    return getFileSizeInMB(file) <= maxSize;
  };
};

const createFileToPreview = (file: File | Blob) => {
  return URL.createObjectURL(file);
};

const createSourceUrl = (src: unknown) => {
  if (typeof src === 'string') {
    return src;
  }

  if (src instanceof File || src instanceof Blob) {
    return createFileToPreview(src);
  }

  return;
};

type AcceptType = Record<MediaType, string[]>;

enum MediaType {
  IMAGES = 'image/*',
  VIDEOS = 'video/*',
  DOCUMENTS = 'application/*',
  ALL = '*',
}

const ACCEPT_TYPES = {
  [MediaType.IMAGES]: ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.avif'],
  [MediaType.VIDEOS]: ['.mp4', '.webm', '.ogg'],
  [MediaType.DOCUMENTS]: ['.pdf', '.doc', '.docx', '.xls', '.xlsx', '.ppt', '.pptx'],
  [MediaType.ALL]: ['.*'],
} as AcceptType;

const getAcceptTypes = (type: MediaType) => {
  return ACCEPT_TYPES[type];
};

const createFileInput = (accept: string, multiple = false) => {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = accept;
  input.multiple = multiple;
  return input;
};

const handleFileSelection = (input: HTMLInputElement): Promise<File | null> => {
  return new Promise((resolve) => {
    input.onchange = (e) => {
      const file = (e.target as HTMLInputElement).files?.[0] ?? null;
      resolve(file);
    };
    input.click();
  });
};

const formatVideoDuration = (seconds: number): string => {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const remainingSeconds = Math.floor(seconds % 60);

  const parts = [];

  if (hours > 0) {
    parts.push(hours.toString());
  }

  parts.push(minutes.toString().padStart(2, '0'));
  parts.push(remainingSeconds.toString().padStart(2, '0'));

  return parts.join(':');
};

const calculateVideoDuration = (url: string) => {
  return new Promise<number>((resolve, reject) => {
    const video = document.createElement('video');
    video.preload = 'metadata';
    video.src = url;
    video.crossOrigin = 'anonymous';

    video.onloadedmetadata = () => {
      resolve(video.duration);
    };

    video.onerror = () => {
      reject(new Error('Failed to load video'));
    };
  });
};

const isMediaObject = (media: unknown): media is MediaAttachment => {
  return typeof media === 'object' && media !== null && 'mime' in media && 'url' in media;
};

const isVideoObject = (media: unknown): media is VideoAttachment => {
  return isMediaObject(media) && typeof media.mime === 'string' && media.mime.startsWith('video/');
};

const toPdfCompatibleImage = async (url: string): Promise<string> => {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.crossOrigin = 'Anonymous';
    img.src = url;

    img.onload = () => {
      try {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;

        const ctx = canvas.getContext('2d');
        if (!ctx) throw new Error('Canvas not supported');
        ctx.drawImage(img, 0, 0);

        const base64 = canvas.toDataURL('image/png', 1.0);
        resolve(base64);
      } catch (error) {
        if (error instanceof Error) {
          reject(error);
          return;
        }

        if (typeof error === 'string') {
          reject(new Error(error));
          return;
        }

        reject(new Error('Unknown error'));
      }
    };

    img.onerror = (error) => {
      if (error instanceof Error) {
        reject(error);
        return;
      }

      if (typeof error === 'string') {
        reject(new Error(error));
        return;
      }

      reject(new Error('Unknown error'));
    };
  });
};

export {
  ACCEPT_TYPES,
  byte2mb,
  calculateVideoDuration,
  createFileInput,
  createFileSizeValidator,
  createFileToPreview,
  createSourceUrl,
  formatVideoDuration,
  getAcceptTypes,
  getFileSize,
  getFileSizeInMB,
  handleFileSelection,
  isMediaObject,
  isVideoObject,
  mb2byte,
  MediaType,
  toPdfCompatibleImage,
  type AcceptType,
};
