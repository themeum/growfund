import { __, sprintf } from '@wordpress/i18n';
import { RefreshCw, Trash, UploadCloud } from 'lucide-react';
import { useCallback, useMemo } from 'react';
import { useDropzone } from 'react-dropzone';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { useMediaUploadMutation } from '@/services/media';
import { isDefined } from '@/utils';
import {
    ACCEPT_TYPES,
    type AcceptType,
    getAcceptTypes,
    mb2byte,
    type MediaType,
} from '@/utils/media';

interface DropZonePropsBasic {
  className?: string;
  value: MediaAttachment | null;
  onError?: (error: string | null) => void;
  maxFiles?: number;
  maxSize?: number; // in bytes
  accept?: MediaType[];
  disabled?: boolean;
  uploadButtonLabel: string;
  dropzoneLabel: string;
}

interface DropZoneProps extends DropZonePropsBasic {
  onChange: (media: MediaAttachment | null) => void;
}

function Media({
  className,
  value,
  onChange,
  onError,
  maxSize = mb2byte(10), // 10MB default
  accept,
  disabled = false,
  uploadButtonLabel,
  dropzoneLabel,
}: DropZoneProps) {
  const { openMediaModal } = useWordpressMedia();
  const { mutateAsync: uploadMedia, isPending } = useMediaUploadMutation();

  const acceptTypes = useMemo<AcceptType>(() => {
    if (!accept) return ACCEPT_TYPES;
    return accept.reduce((acc, type) => {
      acc[type] = getAcceptTypes(type);
      return acc;
    }, {} as AcceptType);
  }, [accept]);

  const handleReplace = () => {
    try {
      openMediaModal({
        title: __('Upload media', 'growfund'),
        button_text: __('Upload', 'growfund'),
        types: ['image'],
        onSelect: (media) => {
          if (!isDefined(media) || media.length === 0) {
            return;
          }
          onChange(media[0]);
        },
      });
    } catch (error) {
      console.error('Error replacing file:', error);
      onError?.(__('Failed to replace file', 'growfund'));
    }
  };

  const handleDropAsync = useCallback(
    async (acceptedFiles: File[], rejectedFiles: unknown[]) => {
      if (rejectedFiles.length > 0) {
        const errors = (
          rejectedFiles as { errors: { code: string }[]; file: { name: string } }[]
        ).map((file) => {
          if (file.errors[0]?.code === 'file-too-large') {
            return sprintf(
              /* translators: 1: file name, 2: max size in MB */
              __('File %1$s is too large. Max size is %2$sMB.', 'growfund'),
              file.file.name,
              maxSize / (1024 * 1024),
            );
          }
          if (file.errors[0]?.code === 'file-invalid-type') {
            /* translators: %s: file name */
            return sprintf(__('File %s has an invalid file type.', 'growfund'), file.file.name);
          }
          /* translators: %s: file name */
          return sprintf(__('File %s could not be uploaded.', 'growfund'), file.file.name);
        });

        if (onError) {
          onError(errors[0]);
        }
        return;
      }

      if (onError) {
        onError(null);
      }

      try {
        const response = await uploadMedia(acceptedFiles);

        if (response.data.media.length > 0) {
          onChange(response.data.media[0]);
        } else {
          onError?.(__('Failed to upload media', 'growfund'));
        }
      } catch (error) {
        console.error('Error uploading media:', error);
        onError?.(__('Failed to upload media', 'growfund'));
      }
    },
    [onError, uploadMedia, maxSize, onChange],
  );

  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: unknown[]) => {
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

  const singlePreview = (preview: MediaAttachment) => {
    return (
      <div className="growfund-group growfund-relative growfund-flex growfund-justify-center growfund-items-center growfund-p-5 growfund-w-full growfund-min-h-64">
        <div className="group-hover:growfund-visible growfund-z-positive growfund-invisible growfund-absolute growfund-left-0 growfund-top-0 growfund-size-full growfund-rounded-md growfund-bg-background-inverse/60">
          <div className="growfund-flex growfund-gap-2 growfund-h-full growfund-items-end growfund-justify-center growfund-pb-6">
            <Button
              variant="ghost"
              size="icon"
              className="growfund-bg-background-fill"
              onClick={() => {
                handleReplace();
              }}
            >
              <RefreshCw />
            </Button>
            <Button
              variant="ghost"
              size="icon"
              className="growfund-bg-background-fill"
              onClick={() => {
                onChange(null);
              }}
            >
              <Trash />
            </Button>
          </div>
        </div>
        <Image
          src={preview.url}
          /* translators: %s: file name */
          alt={sprintf(__('Preview %s', 'growfund'), preview.filename)}
          className="growfund-rounded-md growfund-border-transparent growfund-shadow-none growfund-bg-transparent growfund-max-h-64 growfund-h-full"
          fit="contain"
          aspectRatio="square"
        />
      </div>
    );
  };

  return (
    <div
      className={cn(
        'growfund-border growfund-border-border growfund-bg-background-surface-secondary growfund-rounded-md',
        isDragActive && 'growfund-border-primary growfund-border-dashed growfund-bg-muted/50',
        disabled && 'growfund-opacity-50 growfund-cursor-not-allowed',
        isDragReject && 'growfund-border-border-critical growfund-bg-background-fill-critical-secondary/60',
        className,
      )}
    >
      {isDefined(value) ? (
        <div className={cn('growfund-flex growfund-gap-2 growfund-flex-wrap')}>{singlePreview(value)}</div>
      ) : (
        <div
          {...getRootProps()}
          className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-py-10 growfund-gap-2 growfund-text-center growfund-rounded-md growfund-min-h-[10rem]"
        >
          {isPending ? (
            <LoadingSpinner />
          ) : (
            <>
              <Button
                variant="outline"
                className="growfund-text-fg-primary growfund-typo-sm growfund-font-medium growfund-border-none"
                disabled={disabled}
                onClick={() => {
                  openMediaModal({
                    title: __('Upload media', 'growfund'),
                    button_text: __('Upload', 'growfund'),
                    types: ['image'],
                    onSelect: (media) => {
                      onChange(media[0] ?? null);
                    },
                  });
                }}
              >
                <UploadCloud className="growfund-h-4 growfund-w-4" />
                {uploadButtonLabel}
              </Button>

              <p className="growfund-typo-sm growfund-text-fg-secondary">{dropzoneLabel}</p>
            </>
          )}
        </div>
      )}
    </div>
  );
}

export default Media;
