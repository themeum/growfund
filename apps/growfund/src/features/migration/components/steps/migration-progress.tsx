import { __, sprintf } from '@wordpress/i18n';
import { CheckCircle2, FileHeart, Receipt, SmileIcon } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

import { Alert } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { CircularProgress } from '@/components/ui/circular-progress';
import { Image } from '@/components/ui/image';
import { Progress } from '@/components/ui/progress';
import { growfundConfig } from '@/config/growfund';
import { useMigration } from '@/features/migration/contexts/migration-context';
import {
    Screen,
    ScreenContent,
    ScreenFooter,
    ScreenTitle,
} from '@/features/migration/layouts/screen';
import DecisionBox from '@/features/onboarding/components/decision-box';
import { isDefined } from '@/utils';

const MigrationProgress = () => {
  const { campaignProgress, pledgeProgress } = useMigration();
  const [openAlert, setOpenAlert] = useState(false);

  const campaignProgressPercent = useMemo(() => {
    if (!isDefined(campaignProgress) || campaignProgress.total === 0) {
      return 0;
    }

    return (campaignProgress.completed / campaignProgress.total) * 100;
  }, [campaignProgress]);

  const pledgeProgressPercent = useMemo(() => {
    if (!isDefined(pledgeProgress) || pledgeProgress.total === 0) {
      return 0;
    }

    return (pledgeProgress.completed / pledgeProgress.total) * 100;
  }, [pledgeProgress]);

  useEffect(() => {
    const delayTime = 1 * 60 * 1000;
    const timer = setTimeout(() => {
      setOpenAlert(true);
    }, delayTime);

    // Cleanup timer on unmount
    return () => {
      clearTimeout(timer);
    };
  }, []);

  return (
    <>
      <DecisionBox className="growfund-flex growfund-items-center growfund-justify-center">
        <Image
          src="/images/reward-mode.webp"
          className="growfund-size-full growfund-border-none growfund-max-h-[20.75rem]"
          fit="cover"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenContent>
            <div className="growfund-space-y-3">
              {/* translators: %s: Growfund version */}
              <ScreenTitle>
                {sprintf(__('Migrating to Growfund %s', 'growfund'), growfundConfig.version)}
              </ScreenTitle>
              <div className="growfund-space-y-1">
                <div className="growfund-flex growfund-items-center growfund-justify-between">
                  <p className="growfund-typo-small growfund-text-fg-primary">{__('Progress', 'growfund')}</p>
                  <Badge variant="primary" className="growfund-rounded-full">
                    {`${((campaignProgressPercent + pledgeProgressPercent) / 2).toFixed(0)}%`}
                  </Badge>
                </div>
                <div className="growfund-flex growfund-items-center growfund-gap-1">
                  <Progress value={campaignProgressPercent} />
                  <Progress value={pledgeProgressPercent} />
                </div>
              </div>
            </div>
            <div className="growfund-grid growfund-gap-2 growfund-mt-6">
              <div className="growfund-flex growfund-items-center growfund-rounded-lg growfund-py-2 growfund-px-3 growfund-bg-background-surface growfund-border growfund-border-border growfund-gap-3">
                {campaignProgressPercent < 100 ? (
                  <CircularProgress value={campaignProgressPercent} />
                ) : (
                  <CheckCircle2 className="growfund-fill-icon-brand growfund-text-fg-light" />
                )}
                <div className="growfund-bg-background-surface-tertiary growfund-rounded-md growfund-py-1 growfund-px-2 growfund-flex growfund-items-center growfund-justify-between growfund-w-full">
                  <div className="growfund-flex growfund-items-center growfund-gap-2">
                    <FileHeart className="growfund-text-icon-primary growfund-size-4" />
                    <div className="growfund-typo-small growfund-text-fg-primary growfund-font-medium">
                      {__('Campaigns', 'growfund')}
                    </div>
                  </div>
                  <div className="growfund-typo-small growfund-text-fg-subdued">
                    {campaignProgressPercent === 100
                      ? __('Completed', 'growfund')
                      : /* translators: 1: completed campaigns, 2: total campaigns */
                        sprintf(
                          __('%1$s of %2$s', 'growfund'),
                          campaignProgress?.completed ?? 0,
                          campaignProgress?.total ?? 0,
                        )}
                  </div>
                </div>
              </div>
              <div className="growfund-flex growfund-items-center growfund-rounded-lg growfund-py-2 growfund-px-3 growfund-bg-background-surface growfund-border growfund-border-border growfund-gap-3">
                {pledgeProgressPercent < 100 ? (
                  <CircularProgress value={pledgeProgressPercent} />
                ) : (
                  <CheckCircle2 className="growfund-fill-icon-brand growfund-text-fg-light" />
                )}
                <div className="growfund-bg-background-surface-tertiary growfund-rounded-md growfund-py-1 growfund-px-2 growfund-flex growfund-items-center growfund-justify-between growfund-w-full">
                  <div className="growfund-flex growfund-items-center growfund-gap-2">
                    <Receipt className="growfund-text-icon-primary growfund-size-4" />
                    <div className="growfund-typo-small growfund-text-fg-primary growfund-font-medium">
                      {__('Pledges', 'growfund')}
                    </div>
                  </div>
                  <div className="growfund-typo-small growfund-text-fg-subdued">
                    {pledgeProgressPercent === 100
                      ? __('Completed', 'growfund')
                      : /* translators: 1: completed pledges, 2: total pledges */
                        sprintf(
                          __('%1$s of %2$s', 'growfund'),
                          pledgeProgress?.completed ?? 0,
                          pledgeProgress?.total ?? 0,
                        )}
                  </div>
                </div>
              </div>
            </div>
            <ScreenFooter className="growfund-justify-center growfund-right-0 growfund-px-8">
              <div
                className={`growfund-transition-all growfund-duration-500 growfund-ease-in-out growfund-transform ${
                  openAlert
                    ? 'growfund-opacity-100 translate-y-0'
                    : 'growfund-opacity-0 growfund-translate-y-4 growfund-pointer-events-none'
                }`}
              >
                {openAlert && (
                  <Alert className="growfund-border-l-0 growfund-rounded-lg growfund-bg-background-surface-tertiary">
                    <div className="growfund-flex growfund-items-center growfund-gap-3 growfund-text-fg-brand growfund-font-medium growfund-typo-tiny">
                      <SmileIcon />
                      {__(
                        'This is taking longer than usual, but don’t worry, your data is migrating just fine.',
                        'growfund',
                      )}
                    </div>
                  </Alert>
                )}
              </div>
            </ScreenFooter>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default MigrationProgress;
