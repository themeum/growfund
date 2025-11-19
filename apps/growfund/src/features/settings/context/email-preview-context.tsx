import React, { use } from 'react';

import { type EmailTemplate } from '@/features/settings/schemas/email-default-template';

interface EmailPreviewContextType {
  emailTemplate?: EmailTemplate | null;
}

const EmailPreviewContext = React.createContext<EmailPreviewContextType | null>(null);

const EmailPreviewProvider = ({
  children,
  emailTemplate,
}: React.PropsWithChildren<{ emailTemplate?: EmailTemplate | null }>) => {
  return (
    <EmailPreviewContext
      value={{
        emailTemplate,
      }}
    >
      {children}
    </EmailPreviewContext>
  );
};

const useEmailPreviewContext = () => {
  const context = use(EmailPreviewContext);

  if (!context) {
    throw new Error('useEmailPreviewContext must be used within a EmailPreviewProvider');
  }

  return context;
};

// eslint-disable-next-line react-refresh/only-export-components
export { EmailPreviewProvider, useEmailPreviewContext };
