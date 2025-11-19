import { ExclamationTriangleIcon } from '@radix-ui/react-icons';
import { __ } from '@wordpress/i18n';
import { Image, Video, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { useWordpressMedia } from '@/hooks/use-wp-media';
import { cn } from '@/lib/utils';
import { type VideoField } from '@/schemas/media';
import { isDefined } from '@/utils';
import { calculateVideoDuration, formatVideoDuration } from '@/utils/media';

import { getVideoProvider } from './video-providers';

const LOCAL_VIDEO_PROVIDERS = ['wordpress-media', 'direct'];

function VideoPreview({
  videoField,
  onChange,
}: {
  videoField: VideoField;
  onChange: (video: VideoField | null) => void;
}) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const [videoUrl, setVideoUrl] = useState<string | null>(null);
  const [posterUrl, setPosterUrl] = useState<string | null>(null);
  const [videoDuration, setVideoDuration] = useState<number | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const videoName = typeof videoField.url === 'string' ? videoField.url : videoField.filename;

  const videoProvider = getVideoProvider(videoField);
  const Component = videoProvider?.Component ?? null;

  const { openMediaModal } = useWordpressMedia();

  useEffect(() => {
    if (!videoField.url) {
      setVideoUrl(null);
      setPosterUrl(null);
      return;
    }

    const embedUrl = videoProvider?.getEmbedUrl(videoField.url) ?? null;
    setVideoUrl(embedUrl);
  }, [videoField, videoProvider]);

  useEffect(() => {
    if (!videoProvider || !videoUrl || !LOCAL_VIDEO_PROVIDERS.includes(videoProvider.name)) {
      return;
    }

    void calculateVideoDuration(videoUrl).then((duration) => {
      setVideoDuration(duration);
    });
  }, [videoProvider, videoUrl]);

  useEffect(() => {
    if (!videoField.poster) {
      return;
    }

    setPosterUrl(videoField.poster.url);
  }, [videoField]);

  const handleReplaceVideo = (event: React.MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();

    openMediaModal({
      title: __('Select Video', 'growfund'),
      button_text: __('Select', 'growfund'),
      types: ['video'],
      onSelect: (attachments) => {
        if (attachments.length === 0) {
          return;
        }
        const attachment = attachments[0];
        onChange(attachment);
      },
    });
  };

  const handleAddThumbnail = (event: React.MouseEvent) => {
    event.preventDefault();
    event.stopPropagation();

    openMediaModal({
      title: __('Select Video Thumbnail', 'growfund'),
      button_text: __('Select', 'growfund'),
      types: ['image'],
      onSelect: (attachments) => {
        if (attachments.length === 0) {
          return;
        }
        const attachment = attachments[0];
        onChange({
          ...videoField,
          poster: attachment,
        });
      },
    });
  };

  return (
    <div className="growfund-flex growfund-flex-col">
      <div className="growfund-group growfund-relative growfund-w-full growfund-aspect-video growfund-bg-muted growfund-rounded-t-md growfund-overflow-hidden">
        {videoDuration && (
          <span className="growfund-absolute growfund-bg-background-inverse/60 growfund-px-3 growfund-py-2 growfund-rounded-md growfund-right-2 growfund-top-2 growfund-text-fg-light growfund-typo-tiny growfund-font-medium">
            {formatVideoDuration(videoDuration)}
          </span>
        )}
        {videoProvider && LOCAL_VIDEO_PROVIDERS.includes(videoProvider.name) && (
          <div className="growfund-invisible group-hover:growfund-visible growfund-absolute growfund-left-0 growfund-top-0 growfund-size-full growfund-inset-0 growfund-bg-background-inverse/60 growfund-z-positive growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-gap-1">
            {!videoField.poster ? (
              <Button variant="secondary" className="growfund-px-3 growfund-py-2" onClick={handleAddThumbnail}>
                <Image className="growfund-size-4 growfund-flex-shrink-0 growfund-text-icon-primary" />
                {__('Add Thumbnail', 'growfund')}
              </Button>
            ) : (
              <Button
                variant="destructive-soft"
                className="growfund-px-3 growfund-py-2"
                onClick={() => {
                  onChange({
                    ...videoField,
                    poster: null,
                  });
                  setPosterUrl(null);
                }}
              >
                {__('Remove Thumbnail', 'growfund')}
              </Button>
            )}
            <Button
              variant="link"
              className="growfund-text-fg-light growfund-px-3 growfund-py-2"
              onClick={handleReplaceVideo}
            >
              {__('Replace Video', 'growfund')}
            </Button>
          </div>
        )}
        {!videoUrl && (
          <div className="growfund-flex growfund-flex-col growfund-gap-2 growfund-w-[15.75rem] growfund-mt-[4.375rem] growfund-ml-[3.625rem]">
            <div className="growfund-flex growfund-gap-2 growfund-items-center">
              <ExclamationTriangleIcon className="growfund-size-4" />
              <span className="growfund-typo-small growfund-text-fg-primary">
                {__('Video unavailable', 'growfund')}
              </span>
            </div>
            <span className="growfund-typo-tiny growfund-text-fg-secondary">
              {__("The video link doesn't seem to work. Please recheck your link.", 'growfund')}
            </span>
          </div>
        )}

        {isDefined(Component) ? (
          <Component
            url={videoUrl ?? undefined}
            poster={posterUrl ?? undefined}
            className="growfund-w-full growfund-h-full growfund-object-cover"
          />
        ) : (
          <video
            ref={videoRef}
            src={videoUrl ?? undefined}
            poster={posterUrl ?? undefined}
            className={cn('growfund-size-full growfund-object-cover', isLoading && 'growfund-opacity-0')}
            preload="auto"
            playsInline
            muted
            crossOrigin="anonymous"
            controls={false}
            onError={() => {
              setIsLoading(false);
            }}
          />
        )}
      </div>
      <div className="growfund-typo-sm growfund-text-fg-primary growfund-flex growfund-gap-2 growfund-py-2 growfund-px-3 growfund-items-center growfund-rounded-b-md">
        <Video className="growfund-size-4 growfund-text-icon-primary growfund-flex-shrink-0" />
        <span className="growfund-truncate" title={videoName ?? ''}>
          {videoName ?? ''}
        </span>
        <Button
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
    </div>
  );
}
export default VideoPreview;
