import { __ } from '@wordpress/i18n';
import { ArrowLeft, ArrowRight, Check } from 'lucide-react';

import { GiftPackIcon, GivingThanksIcon } from '@/app/icons';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { Image } from '@/components/ui/image';
import DecisionBox from '@/features/onboarding/components/decision-box';
import {
    type CampaignMode,
    useOnboarding,
} from '@/features/onboarding/contexts/onboarding-context';
import {
    Screen,
    ScreenContent,
    ScreenDescription,
    ScreenFooter,
    ScreenIndicator,
    ScreenTitle,
} from '@/features/onboarding/layouts/screen';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

const campaignModes: {
  mode: CampaignMode;
  title: string;
  description: string;
  icon: React.ElementType;
}[] = [
  {
    mode: 'reward',
    title: __('Reward Based', 'growfund'),
    description: __('Fund projects by offering exclusive rewards & appreciation.', 'growfund'),
    icon: GiftPackIcon,
  },
  {
    mode: 'donation',
    title: __('Donation Based', 'growfund'),
    description: __('Raise funds for causes without offering tangible rewards.', 'growfund'),
    icon: GivingThanksIcon,
  },
];

function getImageSrc(mode: CampaignMode | null) {
  if (mode === 'reward') {
    return `/images/reward-mode.webp`;
  }

  if (mode === 'donation') {
    return `/images/donation-mode.webp`;
  }

  return `/images/mode-selection-empty.webp`;
}

const ModeSelection = () => {
  const { setStep, campaignMode, setCampaignMode } = useOnboarding();

  return (
    <>
      <DecisionBox className="growfund-flex growfund-items-center growfund-justify-center">
        <Image
          src={getImageSrc(campaignMode)}
          className="growfund-size-full growfund-border-none growfund-max-h-[20.75rem]"
          fit="cover"
        />
      </DecisionBox>
      <DecisionBox>
        <Screen className="growfund-space-y-5">
          <ScreenIndicator />
          <ScreenContent>
            <div className="growfund-space-y-3">
              <ScreenTitle>{__('What will you use Growfund for?', 'growfund')}</ScreenTitle>
              <ScreenDescription>
                {__(
                  `Tell us about the type of campaign you plan to launch. We will streamline your experience accordingly.`,
                  'growfund',
                )}
              </ScreenDescription>
            </div>
            <div className="growfund-grid growfund-grid-cols-2 growfund-gap-4 growfund-mt-7">
              {campaignModes.map((mode) => {
                const isActive = mode.mode === campaignMode;
                const Icon = mode.icon;
                return (
                  <Box
                    key={mode.mode}
                    className={cn(
                      'growfund-shadow-none growfund-border growfund-cursor-pointer hover:growfund-border-border-hover',
                      isActive && 'growfund-border-border-brand growfund-bg-[#EBFFF8]',
                    )}
                    onClick={() => {
                      setCampaignMode(mode.mode);
                    }}
                  >
                    <BoxContent className="growfund-flex growfund-flex-col growfund-items-center growfund-justify-center growfund-gap-4 growfund-relative growfund-px-4 growfund-py-4 growfund-text-center">
                      <Icon className="growfund-shrink-0 growfund-size-[4.5rem]" />
                      <div className="growfund-space-y-2">
                        <div className="growfund-typo-tiny growfund-font-medium growfund-text-fg-primary">
                          {mode.title}
                        </div>
                        <p className="growfund-typo-tiny growfund-font-[11px] growfund-text-fg-secondary">
                          {mode.description}
                        </p>
                      </div>

                      {isActive && (
                        <div className="growfund-absolute growfund-top-[-10px] growfund-left-[-10px] growfund-size-5 growfund-bg-background-fill-brand growfund-rounded-full growfund-flex growfund-items-center growfund-justify-center">
                          <Check className="growfund-size-3 growfund-text-white" />
                        </div>
                      )}
                    </BoxContent>
                  </Box>
                );
              })}
            </div>
            <ScreenFooter>
              <Button
                variant="ghost"
                onClick={() => {
                  setStep('welcome');
                }}
              >
                <ArrowLeft />
                {__('Back', 'growfund')}
              </Button>
              <Button
                onClick={() => {
                  setStep('payment-selection');
                }}
                disabled={!isDefined(campaignMode)}
              >
                {__('Continue', 'growfund')}
                <ArrowRight />
              </Button>
            </ScreenFooter>
          </ScreenContent>
        </Screen>
      </DecisionBox>
    </>
  );
};

export default ModeSelection;
