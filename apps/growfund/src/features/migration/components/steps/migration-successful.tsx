import { __ } from '@wordpress/i18n';
import { ArrowRight } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { Screen, ScreenContent } from '@/features/migration/layouts/screen';
import DecisionBox from '@/features/onboarding/components/decision-box';
import { useManageWordpressLayout } from '@/hooks/use-wp-layout';

const MigrationSuccessful = () => {
  const { showWordpressLayout } = useManageWordpressLayout();
  return (
    <>
      <DecisionBox className="growfund-p-3">
        <Image
          src="/images/successful-migration.webp"
          className="growfund-size-full growfund-border-none growfund-bg-transparent"
          fit="cover"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenContent className="growfund-flex growfund-flex-col growfund-justify-center growfund-items-center growfund-gap-6">
            <div className="growfund-flex growfund-flex-col growfund-justify-center growfund-items-center growfund-gap-2">
              <div className="growfund-typo-h4 growfund-text-fg-primary">
                {__('Successfully Migrated!', 'growfund')}
              </div>
              <div className="growfund-text-fg-secondary growfund-typo-small growfund-text-center">
                {__(
                  "Hooray! Your data is safe and sound. Let's dive into Growfund and make some magic happen!",
                  'growfund',
                )}
              </div>
            </div>
            <Button
              variant="primary"
              onClick={(event) => {
                event.preventDefault();
                showWordpressLayout();
                window.location.href = '/wp-admin/admin.php?page=growfund';
              }}
            >
              {__("Let's go", 'growfund')}
              <ArrowRight />
            </Button>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default MigrationSuccessful;
