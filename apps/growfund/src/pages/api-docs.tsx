import { useEffect } from 'react';
import SwaggerUIComponent from 'swagger-ui-react';

import { useManageWordpressLayout } from '@/hooks/use-wp-layout';
import 'swagger-ui-react/swagger-ui.css';

export default function ApiDocsPage() {
  const { hideWordpressLayout, showWordpressLayout } = useManageWordpressLayout();
  useEffect(() => {
    hideWordpressLayout();
    return () => {
      showWordpressLayout();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <div className="growfund-h-[100vh]">
      <SwaggerUIComponent
        url={'http://localhost:5173/openapi.json'}
        deepLinking
        defaultModelsExpandDepth={2}
        defaultModelExpandDepth={3}
        docExpansion="full"
        displayRequestDuration
      />
    </div>
  );
}
