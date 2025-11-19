import { createContext, useContext, useEffect, useState } from 'react';
import { useLocation } from 'react-router';

const ContainerWidthContext = createContext<{
  containerWidth?: number;
  isRefreshed: boolean;
}>({
  containerWidth: undefined,
  isRefreshed: false,
});

const ContainerWidthProvider = ({ children }: { children: React.ReactNode }) => {
  const [containerWidth, setContainerWidth] = useState<number>();
  const [isRefreshed, setIsRefreshed] = useState<boolean>(false);
  const location = useLocation();

  useEffect(() => {
    let resizeObserver: ResizeObserver | null = null;
    const targetSelector = '[data-page-header-container], [data-topbar-container]';

    const observeContainer = (container: Element) => {
      resizeObserver = new ResizeObserver((entries) => {
        for (const entry of entries) {
          setContainerWidth(entry.contentRect.width > 0 ? entry.contentRect.width : undefined);
        }
      });
      resizeObserver.observe(container);
      setIsRefreshed(true);
    };

    const container = document.querySelector(targetSelector);
    if (container) {
      observeContainer(container);
      return () => {
        if (resizeObserver) {
          resizeObserver.disconnect();
        }
        setIsRefreshed(false);
      };
    }

    const mutationObserver = new MutationObserver(() => {
      const container = document.querySelector(targetSelector);
      if (container) {
        observeContainer(container);
      }
    });
    mutationObserver.observe(document.body, { childList: true, subtree: true });
    return () => {
      mutationObserver.disconnect();
      setIsRefreshed(false);
    };
  }, [location.pathname]);

  return (
    <ContainerWidthContext.Provider value={{ containerWidth, isRefreshed }}>
      {children}
    </ContainerWidthContext.Provider>
  );
};

const useContainerWidth = () => useContext(ContainerWidthContext);

// eslint-disable-next-line react-refresh/only-export-components
export { ContainerWidthProvider, useContainerWidth };
