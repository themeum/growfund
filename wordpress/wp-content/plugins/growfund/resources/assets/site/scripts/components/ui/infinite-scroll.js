(function () {
  function observeInfiniteScroll(target, options = {}) {
    if (!target.startsWith('#')) {
      target = `#${target}`;
    }

    const element = document.querySelector(target);

    if (!element) return;

    function loadMore(isIntersecting) {
      document.dispatchEvent(
        new CustomEvent('growfund:infinite-scroll', {
          detail: {
            target,
            loader: element.querySelector('.growfund-infinite-scroll-loader'),
            isIntersecting,
            action: isIntersecting ? 'loadMore' : 'stopped',
          },
        }),
      );
    }

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          loadMore(entries[0].isIntersecting);
        }
      },
      {
        root: options.root ?? null,
        rootMargin: options.rootMargin ?? '0px',
        threshold: options.threshold ?? 1,
      },
    );

    observer.observe(element);
  }

  window.growfundObserveInfiniteScroll = observeInfiniteScroll;
})();
