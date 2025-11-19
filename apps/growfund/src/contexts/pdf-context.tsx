import React, { createContext, useContext } from 'react';

import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { type PdfReceiptTemplate } from '@/features/settings/schemas/pdf-receipt';
import { type AppConfig } from '@/features/settings/schemas/settings';

interface PdfContextType {
  pdfReceiptTemplate?: PdfReceiptTemplate;
  toCurrency: (amount: string | number, withSymbol?: boolean) => string;
  isDonationMode: boolean;
  appConfig: AppConfig;
}

const PdfContext = createContext<PdfContextType | null>(null);

const usePdf = () => {
  const context = useContext(PdfContext);

  if (!context) {
    throw new Error('usePdf must be used within a PdfProvider');
  }

  return context;
};

const PdfProvider = ({
  children,
  pdfReceiptTemplate,
  toCurrency,
  appConfig,
}: React.PropsWithChildren<Omit<PdfContextType, 'isDonationMode'>>) => {
  const isDonationMode = appConfig[AppConfigKeys.DonationMode] === '1';
  return (
    <PdfContext.Provider
      value={{
        pdfReceiptTemplate,
        toCurrency,
        isDonationMode,
        appConfig,
      }}
    >
      {children}
    </PdfContext.Provider>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { PdfProvider, usePdf };
