const createContentSharer = (content: string) => {
  const share = (platform: 'facebook' | 'whatsapp' | 'telegram' | 'linkedin' | 'twitter') => {
    switch (platform) {
      case 'facebook':
        window.open(
          `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(content)}`,
          '_blank',
          'width=600,height=400',
        );
        break;
      case 'twitter':
        window.open(
          `https://x.com/intent/tweet?url=${encodeURIComponent(content)}`,
          '_blank',
          'width=600,height=400',
        );
        break;
      case 'linkedin':
        window.open(
          `https://www.linkedin.com/shareArticle?url=${encodeURIComponent(content)}`,
          '_blank',
          'width=600,height=400',
        );
        break;
      case 'whatsapp':
        window.open(
          `https://wa.me/?text=${encodeURIComponent(content)}`,
          '_blank',
          'width=600,height=400',
        );
        break;
      case 'telegram':
        window.open(
          `https://t.me/share/url?url=${encodeURIComponent(content)}`,
          '_blank',
          'width=600,height=400',
        );
        break;
      default:
        break;
    }
  };

  return { shareOn: share };
};

export { createContentSharer };
