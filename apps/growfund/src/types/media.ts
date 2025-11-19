import { type MediaAttachment } from '@/schemas/media';

type AcceptedMediaTypes = 'image' | 'video' | 'audio' | 'application/pdf';

interface MediaArgs {
  title?: string;
  button?: {
    text?: string;
  };
  multiple?: boolean;
  library?: {
    type?: AcceptedMediaTypes[];
  };
}

interface MediaSelection {
  first(): {
    toJSON(): MediaAttachment;
  };
  map<T>(callback: (item: { toJSON(): MediaAttachment }) => T): T[];
}

interface MediaFrame {
  open: () => void;
  close: () => void;
  on(event: string, callback: () => void): void;
  off(event: string, callback?: () => void): void;
  state(): {
    get(key: 'selection'): MediaSelection;
  };
}

export type { AcceptedMediaTypes, MediaArgs, MediaAttachment, MediaFrame, MediaSelection };
