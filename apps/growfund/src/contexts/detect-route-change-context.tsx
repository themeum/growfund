import { createContext, useContext, useEffect, useRef, useState } from 'react';
import { useLocation } from 'react-router';

interface DetectRouteChangeContextType {
  isRouteChanged: boolean;
}

const DetectRouteChangeContext = createContext<DetectRouteChangeContextType>({
  isRouteChanged: false,
});

const useDetectRouteChange = () =>
  useContext<DetectRouteChangeContextType>(DetectRouteChangeContext);

const DetectRouteChangeProvider = ({ children }: React.PropsWithChildren) => {
  const [isRouteChanged, setIsRouteChanged] = useState(false);
  const isFirstRender = useRef(true);
  const previousLocationRef = useRef(location.pathname);
  const currentLocation = useLocation();

  useEffect(() => {
    if (isFirstRender.current) {
      isFirstRender.current = false;
      previousLocationRef.current = currentLocation.pathname;
      return;
    }

    if (currentLocation.pathname !== previousLocationRef.current) {
      setIsRouteChanged(true);
    } else {
      setIsRouteChanged(false);
    }

    previousLocationRef.current = currentLocation.pathname;
  }, [currentLocation.pathname]);

  return (
    <DetectRouteChangeContext.Provider value={{ isRouteChanged }}>
      {children}
    </DetectRouteChangeContext.Provider>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { DetectRouteChangeProvider, useDetectRouteChange };
