import { __ } from '@wordpress/i18n';
import { UploadCloud } from 'lucide-react';
import React from 'react';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { type MediaAttachment } from '@/schemas/media';

interface GalleryEmptyStateProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'onChange'> {
  uploadButtonLabel: string;
  dropzoneLabel: string;
  disabled: boolean;
  onChange: (attachments: MediaAttachment[]) => void;
  isLoading?: boolean;
}

const GalleryEmptyState = React.forwardRef<HTMLDivElement, GalleryEmptyStateProps>(
  ({ uploadButtonLabel, dropzoneLabel, disabled, onChange, isLoading, ...props }, ref) => {
    const { openMediaModal } = useWordpressMedia();

    return (
      <div
        {...props}
        ref={ref}
        className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-py-10 growfund-gap-2 growfund-text-center growfund-rounded-md growfund-min-h-[10rem]"
      >
        {isLoading ? (
          <LoadingSpinner />
        ) : (
          <>
            <Button
              variant="outline"
              className="growfund-text-fg-primary growfund-typo-sm growfund-font-medium growfund-border-none"
              disabled={disabled}
              onClick={() => {
                openMediaModal({
                  title: __('Select Campaign Images', 'growfund'),
                  multiple: true,
                  button_text: __('Insert', 'growfund'),
                  onSelect: (attachments) => {
                    onChange(attachments);
                  },
                  types: ['image'],
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
    );
  },
);

export default GalleryEmptyState;
