import { __ } from '@wordpress/i18n';
import { ArrowLeft, ArrowRight } from 'lucide-react';
import { useForm, useWatch } from 'react-hook-form';

import { CheckboxField } from '@/components/form/checkbox-field';
import { Button } from '@/components/ui/button';
import { Form } from '@/components/ui/form';
import { Image } from '@/components/ui/image';
import { OptionKeys } from '@/constants/option-keys';
import MigrationNotesDialog from '@/features/migration/components/migration-notes-dialog';
import { useMigration } from '@/features/migration/contexts/migration-context';
import {
    Screen,
    ScreenContent,
    ScreenDescription,
    ScreenFooter,
    ScreenTitle,
} from '@/features/migration/layouts/screen';
import DecisionBox from '@/features/onboarding/components/decision-box';
import { useStoreOptionMutation } from '@/services/app-config';

const Welcome = () => {
  const { setStep, setStart, isCheckedMigrationConsent } = useMigration();
  const storeOptionMutation = useStoreOptionMutation();

  const form = useForm<{ checked_migration_consent: boolean }>({
    defaultValues: {
      checked_migration_consent: isCheckedMigrationConsent,
    },
  });

  const checkedMigrationConsent = useWatch({
    control: form.control,
    name: 'checked_migration_consent',
  });

  return (
    <>
      <DecisionBox className="growfund-p-0">
        <Image
          src="/images/welcome.webp"
          fit="cover"
          className="growfund-border-none growfund-bg-transparent growfund-size-full"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenContent className="growfund-space-y-5">
            <Form {...form}>
              <Image src="/images/migration-banner.webp" fit="cover" />
              <div className="growfund-space-y-3">
                <ScreenTitle>{__('Migrate to Growfund?', 'growfund')}</ScreenTitle>
                <ScreenDescription>
                  {__(
                    'Migrating to Growfund is a great choice! Back up your data before proceeding.',
                    'growfund',
                  )}
                </ScreenDescription>
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <CheckboxField
                    control={form.control}
                    name="checked_migration_consent"
                    wrapperClassName="growfund-w-fit"
                  />
                  <p className="growfund-typo-small growfund-font-medium growfund-text-fg-primary">
                    {__('I confirm that I have backed up my data and reviewed the ', 'growfund')}
                    <MigrationNotesDialog>
                      <span className="growfund-text-fg-emphasis growfund-cursor-pointer">
                        {__('Migration Notes', 'growfund')}
                      </span>
                    </MigrationNotesDialog>
                    {__(' before proceeding.', 'growfund')}
                  </p>
                </div>
              </div>
              <ScreenFooter>
                <div className="growfund-flex growfund-items-center growfund-gap-2">
                  <Button
                    variant="ghost"
                    onClick={() => {
                      setStep('mode-selection');
                    }}
                  >
                    <ArrowLeft />
                    {__('Back', 'growfund')}
                  </Button>
                  <Button
                    onClick={() => {
                      storeOptionMutation.mutate(
                        {
                          key: OptionKeys.CHECKED_MIGRATION_CONSENT,
                          data: checkedMigrationConsent,
                        },
                        {
                          onSuccess() {
                            setStep('progress');
                            setStart(true);
                          },
                        },
                      );
                    }}
                    disabled={!checkedMigrationConsent}
                  >
                    {__('Migrate Now', 'growfund')}
                    <ArrowRight />
                  </Button>
                </div>
              </ScreenFooter>
            </Form>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default Welcome;
