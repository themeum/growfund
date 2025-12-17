import { __ } from '@wordpress/i18n';
import { useFormContext, useWatch } from 'react-hook-form';

import { GiftPackIcon, GivingThanksIcon } from '@/app/icons';
import ElementWrapper from '@/components/element-wrapper';
import SelectionCardField from '@/components/form/selection-card-field';
import { Container } from '@/components/layouts/container';
import ProCheckboxInput from '@/components/pro-fallbacks/form/pro-checkbox-input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { GivingThanks } from '@/features/campaigns/components/giving-thanks/giving-thanks-page';
import GoodiesTab from '@/features/campaigns/components/reward-step/goodies-tab';
import StepNavigation from '@/features/campaigns/components/step-navigation';
import { CampaignRewardProvider } from '@/features/campaigns/contexts/campaign-reward';
import { type CampaignForm } from '@/features/campaigns/schemas/campaign';
import { registry } from '@/lib/registry';
import { type Option } from '@/types';

const RewardsStep = () => {
  const form = useFormContext<CampaignForm>();

  const options: Option<'goodies' | 'giving-thanks'>[] = [
    {
      label: 'Goodies',
      value: 'goodies',
      icon: <GiftPackIcon />,
    },
    {
      label: 'Giving Thanks',
      value: 'giving-thanks',
      icon: <GivingThanksIcon />,
    },
  ];
  const appreciationType = useWatch({
    control: form.control,
    name: 'appreciation_type',
  });

  const CampaignAllowPledgingWithoutReward = registry.get('CampaignAllowPledgingWithoutReward');

  return (
    <Container size="sm">
      <Card>
        <CardHeader>
          <CardTitle>{__('Create Rewards', 'growfund')}</CardTitle>
        </CardHeader>
        <Separator />
        <CardContent className="growfund-mt-4 growfund-space-y-4">
          <SelectionCardField
            control={form.control}
            name="appreciation_type"
            options={options}
            label="Appreciate Contributors by"
          />

          <CampaignRewardProvider>
            {appreciationType === 'goodies' ? (
              <>
                <GoodiesTab />
                <ElementWrapper
                  fallback={
                    <ProCheckboxInput
                      label={__('Allow Pledging Without Rewards', 'growfund')}
                      showProBadge
                    />
                  }
                >
                  {CampaignAllowPledgingWithoutReward && <CampaignAllowPledgingWithoutReward />}
                </ElementWrapper>
              </>
            ) : (
              <GivingThanks />
            )}
          </CampaignRewardProvider>
        </CardContent>
      </Card>
      <div className="growfund-flex growfund-mt-4 growfund-justify-end">
        <StepNavigation />
      </div>
    </Container>
  );
};

export default RewardsStep;
