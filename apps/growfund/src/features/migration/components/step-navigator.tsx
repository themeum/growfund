import MigrationFailed from '@/features/migration/components/steps/migration-failed';
import MigrationModeSelection from '@/features/migration/components/steps/migration-mode-selection';
import MigrationProgress from '@/features/migration/components/steps/migration-progress';
import MigrationSuccessful from '@/features/migration/components/steps/migration-successful';
import Welcome from '@/features/migration/components/steps/welcome';
import { useMigration } from '@/features/migration/contexts/migration-context';

const StepNavigator = () => {
  const { step } = useMigration();

  if (step === 'mode-selection') {
    return <MigrationModeSelection />;
  }

  if (step === 'welcome') {
    return <Welcome />;
  }

  if (step === 'progress') {
    return <MigrationProgress />;
  }

  if (step === 'successful') {
    return <MigrationSuccessful />;
  }

  return <MigrationFailed />;
};

export default StepNavigator;
