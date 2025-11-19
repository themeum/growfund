import { __ } from '@wordpress/i18n';
import { UploadCloud } from 'lucide-react';
import React from 'react';

import { LoadingSpinner } from '@/components/layouts/loading-spinner';
import { Button } from '@/components/ui/button';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { type VideoField } from '@/schemas/media';

interface VideoEmptyStateProps extends Omit<React.HTMLAttributes<HTMLDivElement>, 'onChange'> {
  onChange: (video: VideoField | null) => void;
  isLoading?: boolean;
  disabled?: boolean;
  uploadButtonLabel: string;
  dropzoneLabel: string;
  setIsAddFromUrl: (isAddFromUrl: boolean) => void;
}

const VideoEmptyState = React.forwardRef<HTMLDivElement, VideoEmptyStateProps>(
  (
    { onChange, isLoading, disabled, uploadButtonLabel, dropzoneLabel, setIsAddFromUrl, ...props },
    ref,
  ) => {
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
            <div className="growfund-flex">
              <Button
                variant="outline"
                className="growfund-text-fg-primary growfund-typo-sm growfund-font-medium growfund-border-none"
                disabled={disabled}
                onClick={() => {
                  openMediaModal({
                    title: __('Select Campaign Video', 'growfund'),
                    types: ['video'],
                    onSelect: (attachments) => {
                      if (!attachments.length) {
                        return;
                      }
                      const attachment = attachments[0];
                      onChange(attachment);
                    },
                  });
                }}
              >
                <UploadCloud className="growfund-h-4 growfund-w-4" />
                {uploadButtonLabel}
              </Button>
              <Button
                variant="link"
                className="growfund-text-fg-emphasis hover:growfund-no-underline"
                onClick={(event) => {
                  event.stopPropagation();
                  setIsAddFromUrl(true);
                }}
              >
                {__('Add from URL', 'growfund')}
              </Button>
            </div>
            <p className="growfund-typo-sm growfund-text-fg-secondary">{dropzoneLabel}</p>
          </>
        )}
      </div>
    );
  },
);

export default VideoEmptyState;
