import { __, sprintf } from '@wordpress/i18n';
import { File, Paperclip, UploadCloud, X } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import { type FileRejection, useDropzone } from 'react-dropzone';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { useMediaUploadMutation } from '@/services/media';
import { isDefined } from '@/utils';
import {
  ACCEPT_TYPES,
  type AcceptType,
  byte2mb,
  getAcceptTypes,
  mb2byte,
  type MediaType,
} from '@/utils/media';

interface FileUploaderProps {
  value: MediaAttachment | null;
  onChange: (value: MediaAttachment | null) => void;
  onError?: (error: string | null) => void;
  maxSize?: number;
  disabled?: boolean;
  uploadButtonLabel: string;
  dropzoneLabel: string;
  className?: string;
  accept?: MediaType[];
  isInline: boolean;
}

export function FileUploader({
  value,
  onChange,
  onError,
  maxSize = mb2byte(10),
  disabled = false,
  uploadButtonLabel,
  dropzoneLabel,
  className,
  accept,
  isInline = false,
}: FileUploaderProps) {
  const { mutateAsync: uploadMedia, isPending } = useMediaUploadMutation();
  const { openMediaModal } = useWordpressMedia();

  const acceptTypes = useMemo<AcceptType>(() => {
    if (!accept) return ACCEPT_TYPES;
    return accept.reduce((acc, type) => {
      acc[type] = getAcceptTypes(type);
      return acc;
    }, {} as AcceptType);
  }, [accept]);

  const handleDropAsync = useCallback(
    async (acceptedFiles: File[], rejectedFiles: FileRejection[]) => {
      if (rejectedFiles.length > 0) {
        const error = rejectedFiles[0];

        if (error.errors[0]?.code === 'file-too-large') {
          onError?.(
            sprintf(__('File is too large. Max size is %sMB.', 'growfund'), byte2mb(maxSize)),
          );
        } else {
          onError?.(__('Invalid file type.', 'growfund'));
        }
        return;
      }

      onError?.(null);

      if (!acceptedFiles.length) return;

      try {
        const response = await uploadMedia(acceptedFiles);
        const media = response.data.media[0];

        // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
        if (!media) {
          onError?.(__('Failed to upload file.', 'growfund'));
          return;
        }

        onChange(media);
      } catch {
        onError?.(__('Failed to upload file.', 'growfund'));
      }
    },
    [maxSize, onChange, onError, uploadMedia],
  );

  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: FileRejection[]) => {
      void handleDropAsync(acceptedFiles, rejectedFiles);
    },
    [handleDropAsync],
  );

  const { getRootProps, isDragActive, isDragReject } = useDropzone({
    onDrop,
    maxSize,
    accept: acceptTypes,
    disabled,
    multiple: false,
  });

  const singleFilePreview = (file: MediaAttachment) => {
    return (
      <div
        className={cn(
          'growfund-flex growfund-justify-between growfund-rounded-md growfund-p-2 growfund-items-center growfund-w-full',
          className,
        )}
      >
        <div className="growfund-flex growfund-gap-3 growfund-items-center">
          <div className="growfund-flex growfund-items-center growfund-bg-background-surface-secondary growfund-border growfund-border-border growfund-rounded-sm growfund-p-4">
            <File className="growfund-w-4 growfund-h-5" />
          </div>
          <a
            href={file.url}
            target="_blank"
            rel="noopener noreferrer"
            className="growfund-w-full growfund-typo-small growfund-text-fg-primary hover:growfund-underline"
          >
            {file.filename}
          </a>
        </div>
        <Button
          type="button"
          variant="ghost"
          size="icon"
          className="growfund-h-6 growfund-w-6 growfund-bg-transparent growfund-ml-auto"
          onClick={(event) => {
            event.stopPropagation();
            onChange(null);
          }}
        >
          <X className="growfund-size-4" />
        </Button>
      </div>
    );
  };

  return (
    <div
      {...getRootProps()}
      className={cn(
        'growfund-border growfund-rounded-md growfund-transition-colors growfund-cursor-pointer growfund-bg-background-surface-secondary growfund-overflow-hidden',
        !isInline ? 'growfund-bg-background-surface-secondary' : 'growfund-bg-background-surface',
        isDragActive && 'growfund-border-primary growfund-border-dashed growfund-bg-muted/50',
        isDragReject &&
          'growfund-border-border-critical growfund-bg-background-fill-critical-secondary/60',
        disabled && 'growfund-opacity-50 growfund-cursor-not-allowed',
        className,
      )}
    >
      {isPending ? (
        <div className="growfund-w-full growfund-flex growfund-justify-center growfund-py-4">
          <LoadingSpinner />
        </div>
      ) : isDefined(value) ? (
        singleFilePreview(value)
      ) : !isInline ? (
        <div className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-py-10 growfund-gap-2">
          <Button
            type="button"
            variant="outline"
            className="growfund-text-fg-primary growfund-border-none growfund-bg-white"
            disabled={disabled}
            onClick={(e) => {
              e.stopPropagation();
              openMediaModal({
                title: __('Select File', 'growfund'),
                types: ['application/zip', 'application/pdf'],
                onSelect: (attachments) => {
                  if (attachments.length) {
                    onChange(attachments[0]);
                  }
                },
              });
            }}
          >
            <UploadCloud className="growfund-h-4 growfund-w-4 mr-2" />
            {uploadButtonLabel}
          </Button>
          <p className="growfund-typo-sm growfund-text-fg-secondary">
            {isDragActive ? __('Drop files here', 'growfund') : dropzoneLabel}
          </p>
        </div>
      ) : (
        <Button
          type="button"
          variant="ghost"
          className="growfund-flex growfund-items-center growfund-rounded-md growfund-justify-between growfund-w-full growfund-h-full growfund-text-fg-primary growfund-border-none"
          disabled={disabled}
          onClick={(e) => {
            e.stopPropagation();
            openMediaModal({
              title: __('Select File', 'growfund'),
              types: ['application/zip', 'application/pdf'],
              onSelect: (attachments) => {
                if (attachments.length) {
                  onChange(attachments[0]);
                }
              },
            });
          }}
        >
          <span className="growfund-typo-sm growfund-text-fg-emphasis growfund-font-regular">
            {isDragActive ? __('Drop file here', 'growfund') : __('Select file', 'growfund')}
          </span>
          <Paperclip className="growfund-size-4 growfund-text-icon-primary" />
        </Button>
      )}
    </div>
  );
}
