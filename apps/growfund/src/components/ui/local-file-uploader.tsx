import { __, sprintf } from '@wordpress/i18n';
import { File, Paperclip, UploadCloud, X } from 'lucide-react';
import React, { useCallback, useMemo, useRef } from 'react';
import { useDropzone } from 'react-dropzone';

import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';
import {
  ACCEPT_TYPES,
  type AcceptType,
  getAcceptTypes,
  mb2byte,
  type MediaType,
} from '@/utils/media';

interface LocalFileUploaderProps {
  file?: File | string | null;
  onChange: (value: File | null) => void;
  onError?: (error: string | null) => void;
  maxSize?: number;
  disabled?: boolean;
  uploadButtonLabel: string;
  dropzoneLabel: string;
  className?: string;
  accept?: MediaType[];
  isInline: boolean;
  defaultFileName?: string | null;
  defaultFileUrl?: string;
}

const FilePreview = ({
  file,
  defaultName,
  defaultUrl,
  onRemove,
}: {
  file?: File | string | null;
  defaultName: string;
  defaultUrl?: string;
  onRemove: () => void;
}) => {
  const getUrl = useCallback(
    (file?: File | string | null) => {
      if (!isDefined(file)) {
        return;
      }

      if (typeof file === 'string') {
        if (!isDefined(defaultUrl)) {
          return;
        }

        return defaultUrl;
      }

      if (file instanceof window.File) {
        return URL.createObjectURL(file);
      }
    },
    [defaultUrl],
  );

  const url = getUrl(file);

  return (
    <div className="growfund-flex growfund-justify-between growfund-rounded-md growfund-p-2 growfund-items-center growfund-w-full">
      <div className="growfund-flex growfund-gap-3 growfund-items-center">
        <div className="growfund-flex growfund-items-center growfund-bg-background-surface-secondary growfund-border growfund-border-border growfund-rounded-sm growfund-p-2">
          {file instanceof window.File && file.type.startsWith('image/') ? (
            <Image
              src={url}
              alt={file.name}
              className="growfund-size-10 group-hover:growfund-hidden growfund-border-none growfund-bg-transparent"
              fit="contain"
            />
          ) : (
            <File className="growfund-size-4" />
          )}
        </div>

        {isDefined(url) ? (
          <a
            href={url}
            target="_blank"
            rel="noopener noreferrer"
            className="growfund-w-full growfund-typo-small growfund-text-fg-primary hover:growfund-underline"
          >
            {isDefined(file) ? (typeof file === 'string' ? file : file.name) : defaultName}
          </a>
        ) : (
          <div className="growfund-w-full growfund-typo-small growfund-text-fg-primary hover:growfund-underline">
            {isDefined(file) ? (typeof file === 'string' ? file : file.name) : defaultName}
          </div>
        )}
      </div>
      <Button
        type="button"
        variant="ghost"
        size="icon"
        onClick={(event) => {
          event.stopPropagation();
          onRemove();
        }}
      >
        <X />
      </Button>
    </div>
  );
};

export function LocalFileUploader({
  file,
  onChange,
  onError,
  maxSize = mb2byte(10),
  disabled = false,
  uploadButtonLabel,
  dropzoneLabel,
  className = '',
  accept,
  isInline = false,
  defaultFileName,
  defaultFileUrl,
}: LocalFileUploaderProps) {
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleNativeInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(event.target.files || []);
    if (files.length > 0) {
      handleDrop(files, []);
    }

    if (fileInputRef.current) fileInputRef.current.value = '';
  };

  const acceptTypes = useMemo<AcceptType>(() => {
    if (!accept) return ACCEPT_TYPES;
    return accept.reduce((acc, type) => {
      acc[type] = getAcceptTypes(type);
      return acc;
    }, {} as AcceptType);
  }, [accept]);

  const handleDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: unknown[]) => {
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
        const file = acceptedFiles[0];
        onChange(file);
      } catch (error) {
        console.error('Error uploading media:', error);
        onError?.(__('Failed to upload media', 'growfund'));
      }
    },
    [onError, maxSize, onChange],
  );

  const handleUploadButtonClick = (e: React.MouseEvent) => {
    e.stopPropagation();
    fileInputRef.current?.click();
  };
  const onDrop = useCallback(
    (acceptedFiles: File[], rejectedFiles: unknown[]) => {
      handleDrop(acceptedFiles, rejectedFiles);
    },
    [handleDrop],
  );

  const { getRootProps, isDragActive, isDragReject } = useDropzone({
    onDrop,
    maxSize,
    accept: acceptTypes,
    disabled,
    multiple: false,
  });

  return (
    <div
      className={cn(
        'growfund-border growfund-rounded-md growfund-text-center growfund-cursor-pointer growfund-transition-colors growfund-bg-background-surface-secondary',
        isDragActive && 'growfund-border-primary growfund-border-dashed growfund-bg-muted/50',
        disabled && 'growfund-opacity-50 growfund-cursor-not-allowed',
        isDragReject &&
          'growfund-border-border-critical growfund-bg-background-fill-critical-secondary/60',
        className,
      )}
    >
      <input
        type="file"
        ref={fileInputRef}
        onChange={handleNativeInputChange}
        style={{ display: 'none' }}
        disabled={disabled}
      />

      {isDefined(file) ? (
        <FilePreview
          file={file}
          defaultName={defaultFileName ?? ''}
          defaultUrl={defaultFileUrl}
          onRemove={() => {
            onChange(null);
          }}
        />
      ) : isInline ? (
        <Button
          type="button"
          variant="outline"
          className="growfund-flex growfund-items-center growfund-rounded-md growfund-justify-between growfund-w-full growfund-h-full growfund-text-fg-primary growfund-border-none"
          disabled={disabled}
          onClick={handleUploadButtonClick}
        >
          <span className="growfund-typo-sm growfund-text-fg-emphasis growfund-font-regular">
            {isDragActive ? __('Drop file here', 'growfund') : __('Select file', 'growfund')}
          </span>
          <Paperclip className="growfund-size-4 growfund-text-icon-primary" />
        </Button>
      ) : (
        <div
          {...getRootProps()}
          className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-py-10 growfund-gap-2"
        >
          <Button
            type="button"
            variant="outline"
            className="growfund-text-fg-primary growfund-typo-sm growfund-font-medium growfund-border-none"
            disabled={disabled}
            onClick={handleUploadButtonClick}
          >
            <UploadCloud className="growfund-h-4 growfund-w-4" />
            {uploadButtonLabel}
          </Button>

          <p className="growfund-typo-sm growfund-text-fg-secondary">{dropzoneLabel}</p>
        </div>
      )}
    </div>
  );
}
