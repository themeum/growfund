import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useMemo } from 'react';
import { type FileRejection, useDropzone } from 'react-dropzone';

import GalleryEmptyState from '@/components/form/gallery-field/gallery-empty-state';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { cn } from '@/lib/utils';
import { type MediaAttachment } from '@/schemas/media';
import { useMediaUploadMutation } from '@/services/media';
import { arrayUniqueBy } from '@/utils';
import {
    ACCEPT_TYPES,
    type AcceptType,
    getAcceptTypes,
    mb2byte,
    type MediaType,
} from '@/utils/media';

import { Gallery } from './gallery';

interface DropZoneProps {
  className?: string;
  value: MediaAttachment[];
  onError?: (error: string | null) => void;
  maxFiles?: number;
  maxSize?: number; // in bytes
  accept?: MediaType[];
  disabled?: boolean;
  uploadButtonLabel: string;
  dropzoneLabel: string;
  onChange: (files: MediaAttachment[]) => void;
}

function ImageGallery({
  className,
  value = [],
  onChange,
  onError,
  maxSize = mb2byte(10), // 10MB default
  accept,
  disabled = false,
  uploadButtonLabel,
  dropzoneLabel,
}: DropZoneProps) {
  const acceptTypes = useMemo<AcceptType>(() => {
    if (!accept) return ACCEPT_TYPES;
    return accept.reduce((acc, type) => {
      acc[type] = getAcceptTypes(type);
      return acc;
    }, {} as AcceptType);
  }, [accept]);
  const { mutateAsync: uploadMedia, isPending } = useMediaUploadMutation();

  const handleDropAsync = useCallback(
    async (acceptedFiles: File[], rejectedFiles: FileRejection[]) => {
      if (rejectedFiles.length > 0) {
        const errors = rejectedFiles.map((file) => {
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
          onChange(arrayUniqueBy([...value, ...response.data.media], 'id'));
        } else {
          onError?.(__('Failed to upload media', 'growfund'));
        }
      } catch (error) {
        console.error('Error uploading images:', error);
        onError?.(__('Failed to upload media', 'growfund'));
      }
    },
    [maxSize, onChange, onError, uploadMedia, value],
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
    multiple: true,
  });

  const { openMediaModal } = useWordpressMedia();

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
      {value.length ? (
        <div className="growfund-flex growfund-flex-wrap growfund-gap-2">
          <Gallery
            key={value.length}
            images={value.map((item, index) => {
              return {
                ...item,
                featured: index === 0,
              };
            })}
            onChange={onChange}
            onUpload={() => {
              openMediaModal({
                title: __('Select Campaign Images', 'growfund'),
                multiple: true,
                button_text: __('Insert', 'growfund'),
                onSelect: (attachments) => {
                  onChange(arrayUniqueBy([...value, ...attachments], 'id'));
                },
                types: ['image'],
              });
            }}
            isLoading={isPending}
          />
        </div>
      ) : (
        <GalleryEmptyState
          {...getRootProps()}
          uploadButtonLabel={uploadButtonLabel}
          dropzoneLabel={dropzoneLabel}
          disabled={disabled}
          onChange={onChange}
          isLoading={isPending}
        />
      )}
    </div>
  );
}

export default ImageGallery;
