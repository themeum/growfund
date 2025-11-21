import { __ } from '@wordpress/i18n';
import { Headphones } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import { Screen, ScreenContent } from '@/features/migration/layouts/screen';
import DecisionBox from '@/features/onboarding/components/decision-box';

const MigrationFailed = () => {
  return (
    <>
      <DecisionBox className="growfund-p-3">
        <Image
          src="/images/migration-failed.webp"
          className="growfund-size-full growfund-border-none growfund-bg-transparent"
          fit="cover"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenContent className="growfund-flex growfund-flex-col growfund-justify-center growfund-items-center growfund-gap-6">
            <div className="growfund-flex growfund-flex-col growfund-justify-center growfund-items-center growfund-gap-2">
              <div className="growfund-typo-h4 growfund-text-fg-critical">
                {__('Migration Failed', 'growfund')}
              </div>
              <div className="growfund-text-fg-subdued growfund-typo-small growfund-text-center">
                {__(
                  'We apologize, the migration was unsuccessful. Please contact support for assistance.',
                  'growfund',
                )}
              </div>
            </div>
            <Button
              variant="outline"
              onClick={(event) => {
                event.preventDefault();
                window.location.href = 'https://growfund.com/support';
              }}
            >
              <Headphones />
              {__('Contact Support', 'growfund')}
            </Button>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default MigrationFailed;
