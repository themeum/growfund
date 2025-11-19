import { __ } from '@wordpress/i18n';
import { cva, type VariantProps } from 'class-variance-authority';
import { X } from 'lucide-react';
import React, { useEffect, useState } from 'react';

import placeholder from '@/assets/images/placeholder.svg';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

import { Skeleton } from './skeleton';

const imageVariants = cva('growfund-object-cover growfund-transition-opacity growfund-duration-300', {
  variants: {
    rounded: {
      none: 'growfund-rounded-none',
      sm: 'growfund-rounded-sm',
      md: 'growfund-rounded-md',
      lg: 'growfund-rounded-lg',
      xl: 'growfund-rounded-xl',
      full: 'growfund-rounded-full',
    },
    aspectRatio: {
      auto: 'growfund-aspect-auto',
      square: 'growfund-aspect-square',
      video: 'growfund-aspect-video',
      portrait: 'growfund-aspect-[3/4]',
      wide: 'growfund-aspect-[16/9]',
    },
    fit: {
      cover: 'growfund-object-cover',
      contain: 'growfund-object-contain',
      fill: 'growfund-object-fill',
      none: 'growfund-object-none',
    },
  },
  defaultVariants: {
    rounded: 'md',
    aspectRatio: 'auto',
    fit: 'cover',
  },
});

interface ImageProps
  extends Omit<React.ImgHTMLAttributes<HTMLImageElement>, 'src'>,
    VariantProps<typeof imageVariants> {
  fallbackSrc?: string;
  showSkeleton?: boolean;
  src?: string | null;
}

function processSrc(src: string | null | undefined) {
  if (!isDefined(src)) {
    return null;
  }

  if (src.startsWith('/')) {
    return `${window.growfund.assets_url}${src}`;
  }

  return src;
}

const Image = React.forwardRef<HTMLImageElement, ImageProps>(
  (
    {
      className,
      src,
      alt = '',
      rounded,
      aspectRatio,
      fit,
      fallbackSrc = placeholder,
      showSkeleton = true,
      ...props
    },
    ref,
  ) => {
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(false);

    const handleLoad = () => {
      setIsLoading(false);
    };

    const handleError = () => {
      setIsLoading(false);
      setError(true);
    };

    useEffect(() => {
      if (src && error) {
        setError(false);
      }
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [src]);

    return (
      <div
        className={cn(
          'growfund-relative growfund-overflow-hidden growfund-bg-background-surface-subdued growfund-border growfund-border-border growfund-shrink-0',
          imageVariants({ rounded, aspectRatio, fit: 'none' }),
          className,
        )}
      >
        {isLoading && showSkeleton && <Skeleton className="growfund-absolute growfund-inset-0 growfund-z-10" />}
        {error ? (
          <div className="growfund-flex growfund-h-full growfund-w-full growfund-flex-col growfund-items-center growfund-justify-center growfund-bg-background-surface-subdued">
            {fallbackSrc ? (
              <img src={fallbackSrc} alt={alt} className={cn(imageVariants({ rounded, fit }))} />
            ) : (
              <div className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-p-4 growfund-text-fg-muted">
                <X className="growfund-h-8 growfund-w-8 growfund-mb-2" />
                <span className="growfund-typo-small">{__('Failed to load image', 'growfund')}</span>
              </div>
            )}
          </div>
        ) : (
          <img
            ref={ref}
            src={processSrc(src) ?? fallbackSrc}
            alt={alt}
            className={cn(
              'growfund-w-full growfund-h-full',
              imageVariants({ rounded: 'none', fit }),
              isLoading ? 'growfund-opacity-0' : 'growfund-opacity-100',
            )}
            onLoad={handleLoad}
            onError={handleError}
            {...props}
          />
        )}
      </div>
    );
  },
);

Image.displayName = 'Image';

// eslint-disable-next-line react-refresh/only-export-components
export { Image, processSrc };
