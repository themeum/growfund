import { sprintf } from '@wordpress/i18n';
import { useMemo } from 'react';

import { LoadingSpinnerOverlay } from '@/components/layouts/loading-spinner';
import { RouteConfig } from '@/config/route-config';
import EmailLayoutContent from '@/features/settings/components/email-layout-content';
import { emailTemplates } from '@/features/settings/schemas/email-content';
import { useRouteParams } from '@/hooks/use-route-params';
import { useGetEmailContentQuery } from '@/services/app-config';
import { isDefined } from '@/utils';

const GlobalEmails = () => {
  const { emailType, userType } = useRouteParams(RouteConfig.EmailContents);
  const currentKey = sprintf('%s_%s', userType, emailType.replace(/-/g, '_'));
  const currentTemplate = emailTemplates.find((template) => template.key === currentKey);

  const emailOptionKey = currentTemplate?.optionKey ?? '';
  const pageHeader = currentTemplate?.label ?? '';

  const emailContentQuery = useGetEmailContentQuery(emailOptionKey);

  const emailContent = useMemo(() => {
    if (!isDefined(emailContentQuery.data)) {
      return null;
    }

    return emailContentQuery.data;
  }, [emailContentQuery.data]);

  if (emailContentQuery.isLoading || emailContentQuery.isPending) {
    return <LoadingSpinnerOverlay />;
  }

  return (
    <EmailLayoutContent
      pageHeader={pageHeader}
      emailOptionKey={emailOptionKey}
      emailContent={emailContent}
    />
  );
};

export default GlobalEmails;
