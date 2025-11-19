import { createContext, use, useEffect, useState } from 'react';

import { type Migrate, type MigrationProcess } from '@/features/migration/schemas/migrate';
import { useMigrationMutation } from '@/features/migration/services/migrate';

export type MigrationStep = 'mode-selection' | 'welcome' | 'progress' | 'successful' | 'failed';

interface MigrationContextType {
  step: MigrationStep;
  setStep: (step: MigrationStep) => void;
  process: MigrationProcess;
  setProcess: (step: MigrationProcess) => void;
  campaignProgress?: Migrate;
  pledgeProgress?: Migrate;
  setStart: (start: boolean) => void;
  isCheckedMigrationConsent: boolean;
}

const MigrationContext = createContext<MigrationContextType | null>(null);

const useMigration = () => {
  const context = use(MigrationContext);
  if (!context) {
    throw new Error('useMigration must be used within an MigrationProvider');
  }
  return context;
};

const MigrationProvider = ({
  children,
  isCheckedMigrationConsent = false,
}: {
  children: React.ReactNode;
  isCheckedMigrationConsent?: boolean;
}) => {
  const [step, setStep] = useState<MigrationStep>('mode-selection');
  const [process, setProcess] = useState<MigrationProcess>('migrate-campaigns');
  const [campaignProgress, setCampaignProgress] = useState<Migrate>();
  const [pledgeProgress, setPledgeProgress] = useState<Migrate>();
  const [start, setStart] = useState(false);

  const migrationMutation = useMigrationMutation();

  function sleep(): Promise<void> {
    return new Promise((resolve) => setTimeout(resolve, 100));
  }

  const startMigration = async (process: MigrationProcess) => {
    try {
      const response = await migrationMutation.mutateAsync(process);
      await sleep();
      const data = response.data as Migrate;

      if (data.step === 'migrate-campaigns') {
        setCampaignProgress(data);
      }

      if (data.step === 'migrate-contributions') {
        setPledgeProgress(data);
      }

      if (data.is_running) {
        // Recursively call next chunk
        await startMigration(process);
        return;
      }

      if (data.step === 'migrate-campaigns') {
        setProcess('migrate-contributions');
        return;
      }

      if (data.step === 'migrate-contributions') {
        setProcess('final');
        return;
      }

      setStep('successful');
    } catch {
      setStep('failed');
    }
  };

  useEffect(() => {
    if (start) {
      void startMigration(process);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [start, process]);

  return (
    <MigrationContext
      value={{
        step,
        setStep,
        process,
        setProcess,
        campaignProgress,
        pledgeProgress,
        setStart,
        isCheckedMigrationConsent,
      }}
    >
      {children}
    </MigrationContext>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export { MigrationProvider, useMigration };
