import { useEffect } from 'react';

import TemplateLayoutContent from '@/features/settings/components/template-layout-content';
import { TemplateLayoutProvider } from '@/features/settings/context/template-layout-context';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';

const TemplateBuilderLayout = () => {
  const { hideWordpressLayout, showWordpressLayout } = useManageWordpressLayout();

  useEffect(() => {
    hideWordpressLayout();
    return () => {
      showWordpressLayout();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <TemplateLayoutProvider>
      <TemplateLayoutContent />
    </TemplateLayoutProvider>
  );
};

export default TemplateBuilderLayout;
