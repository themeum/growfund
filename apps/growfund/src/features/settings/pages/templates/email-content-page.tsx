import { useEffect } from 'react';

import { RouteConfig } from '@/config/route-config';
import GlobalEmails from '@/features/settings/components/email-contents/global-emails';
import { useRouteParams } from '@/hooks/use-route-params';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';

const EmailContentPage = () => {
  const { emailType } = useRouteParams(RouteConfig.EmailContents);
  const { hideWordpressLayout, showWordpressLayout } = useManageWordpressLayout();

  useEffect(() => {
    hideWordpressLayout();
    return () => {
      showWordpressLayout();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!emailType) {
    return <div>Not Found</div>;
  }

  return <GlobalEmails />;
};

export default EmailContentPage;
