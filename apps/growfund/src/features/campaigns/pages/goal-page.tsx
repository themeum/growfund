import { __ } from '@wordpress/i18n';
import { useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { SelectField } from '@/components/form/select-field';
import { SwitchField } from '@/components/form/switch-field';
import { TextField } from '@/components/form/text-field';
import { Container } from '@/components/layouts/container';
import CampaignAllowCustomDonationFallback from '@/components/pro-fallbacks/campaign/campaign-allow-custom-donation-fallback';
import CampaignGoalReachingActionFallback from '@/components/pro-fallbacks/campaign/campaign-goal-reaching-action-fallbacks';
import { Box, BoxContent } from '@/components/ui/box';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { useAppConfig } from '@/contexts/app-config';
import DonationPresets from '@/features/campaigns/components/goal-step/donation-presets';
import StepNavigation from '@/features/campaigns/components/step-navigation';
import {
  type CampaignBuilderForm,
  type CampaignGoalType,
} from '@/features/campaigns/schemas/campaign';
import { registry } from '@/lib/registry';

const getGoalTypeOptions = (isDonationMode: boolean) => {
  return [
    {
      value: 'raised-amount',
      label: __('Raised Amount', 'growfund'),
    },
    {
      value: 'number-of-contributions',
      label: isDonationMode ? __('Donation Numbers', 'growfund') : __('Pledge Numbers', 'growfund'),
    },
    {
      value: 'number-of-contributors',
      label: isDonationMode ? __('Donors Numbers', 'growfund') : __('Backers Numbers', 'growfund'),
    },
  ];
};

const getGoalAmountInputInfo = (isDonationMode: boolean) => {
  return new Map<
    CampaignGoalType,
    { label: string; placeholder: string; fieldType: 'number' | 'text' }
  >([
    [
      'raised-amount',
      {
        label: __('Target Goal', 'growfund'),
        placeholder: __('e.g. 50.00', 'growfund'),
        fieldType: 'number',
      },
    ],
    [
      'number-of-contributions',
      {
        label: isDonationMode
          ? __('Donation Numbers', 'growfund')
          : __('Pledge Numbers', 'growfund'),
        placeholder: __('e.g. 10', 'growfund'),
        fieldType: 'number',
      },
    ],
    [
      'number-of-contributors',
      {
        label: isDonationMode
          ? __('Donors Numbers', 'growfund')
          : __('Backers Numbers', 'growfund'),
        placeholder: __('e.g. 10', 'growfund'),
        fieldType: 'number',
      },
    ],
  ]);
};

const GoalStep = () => {
  const { isDonationMode } = useAppConfig();
  const form = useFormContext<CampaignBuilderForm>();
  const hasCampaignGoal = useWatch({ control: form.control, name: 'has_goal' });
  const goalType = useWatch({ control: form.control, name: 'goal_type' });

  const goalAmountField = useMemo(() => {
    const goalAmountInputInfo = getGoalAmountInputInfo(isDonationMode);
    return goalType ? goalAmountInputInfo.get(goalType) : null;
  }, [goalType, isDonationMode]);

  const CampaignGoalReachingAction = registry.get('CampaignGoalReachingAction');
  const CampaignAllowCustomDonation = registry.get('CampaignAllowCustomDonation');

  return (
    <Container size="xs" className="growfund-grid growfund-gap-4">
      <Card>
        <CardHeader>
          <CardTitle>{__('Goal', 'growfund')}</CardTitle>
        </CardHeader>
        <Separator />
        <CardContent className="growfund-mt-4 growfund-grid growfund-gap-4">
          <Box className="growfund-shadow-none">
            <BoxContent className="growfund-grid growfund-gap-4">
              <SwitchField
                control={form.control}
                name="has_goal"
                label={__('Campaign Goal', 'growfund')}
              />

              {hasCampaignGoal && (
                <div className="growfund-space-y-4">
                  <div className="growfund-flex growfund-gap-4 growfund-items-start">
                    <SelectField
                      control={form.control}
                      name="goal_type"
                      label={__('Goal Type', 'growfund')}
                      placeholder={__('Select a goal type', 'growfund')}
                      options={getGoalTypeOptions(isDonationMode)}
                    />

                    {goalAmountField && (
                      <TextField
                        control={form.control}
                        name="goal_amount"
                        label={goalAmountField.label}
                        placeholder={goalAmountField.placeholder}
                        type={goalAmountField.fieldType}
                        key={'goal_amount'}
                      />
                    )}
                  </div>
                  <ElementWrapper fallback={<CampaignGoalReachingActionFallback />}>
                    {CampaignGoalReachingAction && <CampaignGoalReachingAction />}
                  </ElementWrapper>
                </div>
              )}
            </BoxContent>
          </Box>

          {isDonationMode && (
            <Box className="growfund-shadow-none">
              <BoxContent className="growfund-space-y-4">
                <DonationPresets />
                <ElementWrapper fallback={<CampaignAllowCustomDonationFallback />}>
                  {CampaignAllowCustomDonation && <CampaignAllowCustomDonation />}
                </ElementWrapper>
              </BoxContent>
            </Box>
          )}
        </CardContent>
      </Card>

      <div className="growfund-flex growfund-mt-4 growfund-justify-end">
        <StepNavigation />
      </div>
    </Container>
  );
};

export default GoalStep;
