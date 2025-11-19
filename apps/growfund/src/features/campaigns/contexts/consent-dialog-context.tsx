import React, { createContext, use, useCallback, useMemo, useState } from 'react';

import ConsentDialog from '@/components/dialogs/consent';
import { type ButtonVariants } from '@/components/ui/button';
import { isDefined } from '@/utils';

interface ConsentDialogOptions {
  title: string | React.ReactNode;
  content: string | React.ReactNode;
  showCheckBox?: boolean;
  onConfirm: (callback: () => void, isDeleteAssociatedData: boolean) => Promise<void> | void;
  onDecline?: () => void;
  confirmText?: string;
  declineText?: string;
  checkBoxLabel?: string;
  checkBox?: React.ReactNode;
  confirmButtonVariant?: ButtonVariants;
  declineButtonVariant?: ButtonVariants;
}

interface ConsentDialogContextType {
  open: boolean;
  setOpen: (open: boolean) => void;
  openDialog: (options: ConsentDialogOptions) => void;
  checkboxLabel: React.ReactNode;
  setCheckboxLabel: (label: React.ReactNode) => void;
}

const ConsentDialogContext = createContext<ConsentDialogContextType | null>(null);

const useConsentDialog = () => {
  const context = use(ConsentDialogContext);
  if (!context) {
    throw new Error('useConsentDialog must be used within a ConsentDialogProvider');
  }
  return context;
};

const ConsentDialogProvider = ({ children }: { children: React.ReactNode }) => {
  const [open, setOpen] = useState(false);
  const [options, setOptions] = useState<ConsentDialogOptions | null>(null);
  const [loading, setLoading] = useState(false);
  const [checkboxChecked, setCheckboxChecked] = useState(false);
  const [checkboxLabel, setCheckboxLabel] = useState<React.ReactNode>('');

  const openDialog = useCallback((options: ConsentDialogOptions) => {
    setOptions({
      ...options,
      showCheckBox: options.showCheckBox ?? false,
    });
    setOpen(true);
  }, []);

  const closeDialog = useCallback(() => {
    setLoading(false);
    setOpen(false);
  }, []);

  const contextValue = useMemo(() => {
    return {
      open,
      setOpen,
      openDialog,
      checkboxLabel,
      setCheckboxLabel,
    };
  }, [open, setOpen, openDialog, checkboxLabel, setCheckboxLabel]);

  return (
    <ConsentDialogContext value={contextValue}>
      {children}
      {isDefined(options) && (
        <ConsentDialog
          open={open}
          onOpenChange={setOpen}
          title={options.title}
          content={options.content}
          checkboxContent={options.checkBox}
          checkboxLabel={options.checkBoxLabel}
          checkboxChecked={checkboxChecked}
          setCheckboxChecked={setCheckboxChecked}
          onConfirm={async () => {
            setLoading(true);
            try {
              await options.onConfirm(closeDialog, checkboxChecked);
            } catch {
              closeDialog();
            } finally {
              closeDialog();
            }
          }}
          onDecline={() => {
            options.onDecline?.();
            setLoading(false);
            setOpen(false);
          }}
          confirmText={options.confirmText}
          declineText={options.declineText}
          confirmButtonVariant={options.confirmButtonVariant}
          declineButtonVariant={options.declineButtonVariant}
          loading={loading}
          showCheckBox={options.showCheckBox}
        />
      )}
    </ConsentDialogContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { ConsentDialogProvider, useConsentDialog };
