import React, { useEffect, useState } from 'react';

import { Image as ImageComp } from './image';

interface SmartImageProps extends Omit<React.ImgHTMLAttributes<HTMLImageElement>, 'src'> {
  src: string | null;
  alt: string;
  maxHeight?: number;
  aspectRatio?: number;
}

const SmartImage = React.forwardRef<HTMLImageElement, SmartImageProps>(
  ({ src, alt, className, maxHeight = 256, aspectRatio = 16 / 9, ...props }, ref) => {
    const [dimensions, setDimensions] = useState({ width: 0, height: 0 });

    useEffect(() => {
      if (!src) return;
      const img = new Image();
      img.src = src;
      img.onload = () => {
        setDimensions({ width: img.width, height: img.height });
      };
    }, [src]);

    const maxWidth = maxHeight * aspectRatio;
    const isSmaller = dimensions.width < maxWidth || dimensions.height < maxHeight;
    const fit = isSmaller ? 'contain' : 'cover';

    return <ImageComp ref={ref} src={src} alt={alt} fit={fit} className={className} {...props} />;
  },
);

SmartImage.displayName = 'SmartImage';

export { SmartImage };
