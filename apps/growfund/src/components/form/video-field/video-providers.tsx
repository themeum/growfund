import { type VideoField } from '@/schemas/media';

export type VideoProviderName = 'wordpress-media' | 'direct' | 'youtube' | 'vimeo';

interface VideoProvider {
  name: VideoProviderName;
  matches: (url: string) => boolean;
  getEmbedUrl: (url: string) => string | null;
  Component: React.ComponentType<{ url: string | undefined; className?: string; poster?: string }>;
}

const WordPressMediaProvider: VideoProvider = {
  name: 'wordpress-media',
  matches: (url) => {
    return url.includes('wp-content/uploads');
  },
  getEmbedUrl: (url) => {
    return url;
  },
  Component: ({ url, className, poster }) => {
    return <video src={url} className={className} preload="metadata" poster={poster} />;
  },
};

// Default video provider for direct video URLs (mp4, webm, etc)
const DirectVideoProvider: VideoProvider = {
  name: 'direct',
  matches: (url) => {
    try {
      const urlObj = new URL(url);
      return (
        /\.(mp4|webm|ogg)$/i.exec(urlObj.pathname) !== null && !url.includes('/wp-content/uploads')
      );
    } catch {
      return false;
    }
  },
  getEmbedUrl: (url) => url,
  Component: ({ url, className, poster }) => {
    return <video src={url} className={className} preload="metadata" poster={poster} />;
  },
};

const YouTubeProvider: VideoProvider = {
  name: 'youtube',
  matches: (url) => {
    try {
      const urlObj = new URL(url);
      return urlObj.hostname.includes('youtube.com') || urlObj.hostname.includes('youtu.be');
    } catch {
      return false;
    }
  },
  getEmbedUrl: (url) => {
    try {
      const urlObj = new URL(url);
      let videoId = '';

      if (urlObj.hostname.includes('youtube.com')) {
        videoId = urlObj.searchParams.get('v') ?? '';
      } else if (urlObj.hostname.includes('youtu.be')) {
        videoId = urlObj.pathname.slice(1);
      }

      return videoId ? `https://www.youtube.com/embed/${videoId}` : null;
    } catch {
      return null;
    }
  },
  Component: ({ url, className }) => (
    <iframe
      src={url}
      className={className}
      allowFullScreen
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      style={{ pointerEvents: 'none' }}
    />
  ),
};
const VimeoProvider: VideoProvider = {
  name: 'vimeo',
  matches: (url) => {
    try {
      const urlObj = new URL(url);
      return urlObj.hostname.includes('vimeo.com') || urlObj.hostname.includes('player.vimeo.com');
    } catch {
      return false;
    }
  },
  getEmbedUrl: (url) => {
    try {
      const urlObj = new URL(url);
      let videoId = '';

      if (urlObj.hostname.includes('vimeo.com')) {
        videoId = urlObj.pathname.split('/').filter(Boolean).pop() ?? '';
      }

      return videoId ? `https://player.vimeo.com/video/${videoId}` : null;
    } catch {
      return null;
    }
  },
  Component: ({ url, className }) => (
    <iframe
      src={url}
      className={className}
      allowFullScreen
      allow="autoplay; fullscreen; picture-in-picture"
      style={{ pointerEvents: 'none' }}
    />
  ),
};

export const videoProviders: VideoProvider[] = [
  WordPressMediaProvider,
  YouTubeProvider,
  VimeoProvider,
  DirectVideoProvider,
];

export const getVideoProvider = (videoData: VideoField): VideoProvider | null => {
  return typeof videoData.url === 'string'
    ? videoProviders.find((provider) => provider.matches(videoData.url)) ?? null
    : null;
};
