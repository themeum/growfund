import { type InfiniteData, type UseInfiniteQueryResult } from '@tanstack/react-query';
import { __ } from '@wordpress/i18n';
import { useCallback, useEffect, useRef } from 'react';

interface InfiniteQueryScrollProps<T> {
  query: UseInfiniteQueryResult<InfiniteData<T>>;
  rootRef?: React.RefObject<HTMLElement | null> | null;
  loadingText?: string;
  noMoreText?: string;
  showNoMore?: boolean;
}

const InfiniteQueryScroll = <T,>({
  rootRef = null,
  query,
  loadingText = __('Loading...', 'growfund'),
  noMoreText = __('No more records to load.', 'growfund'),
  showNoMore = false,
}: InfiniteQueryScrollProps<T>) => {
  const observerRef = useRef<HTMLDivElement | null>(null);
  const isFetchingLock = useRef(false);
  const { hasNextPage, isFetchingNextPage, fetchNextPage } = query;

  const callFetchNextPage = useCallback(() => {
    if (hasNextPage && !isFetchingNextPage && !isFetchingLock.current) {
      isFetchingLock.current = true;
      void fetchNextPage().finally(() => {
        isFetchingLock.current = false;
      });
    }
  }, [fetchNextPage, hasNextPage, isFetchingNextPage]);

  useEffect(() => {
    if (!hasNextPage) return;

    const observer = new IntersectionObserver(
      (entries) => {
        const [entry] = entries;
        if (entry.isIntersecting) {
          callFetchNextPage();
        }
      },
      {
        root: rootRef?.current ?? null,
        rootMargin: '0px',
        threshold: 1.0,
      },
    );

    const current = observerRef.current;
    if (current) observer.observe(current);

    return () => {
      if (current) observer.unobserve(current);
      observer.disconnect();
    };
  }, [callFetchNextPage, hasNextPage, rootRef]);

  return (
    <>
      <div ref={observerRef} />

      {isFetchingNextPage && (
        <p className="growfund-text-center growfund-typo-tiny growfund-text-fg-secondary growfund-mt-4">{loadingText}</p>
      )}

      {!hasNextPage && showNoMore && (
        <p className="growfund-type-tiny growfund-text-center growfund-text-fg-secondary growfund-mt-4">{noMoreText}</p>
      )}
    </>
  );
};

export default InfiniteQueryScroll;
