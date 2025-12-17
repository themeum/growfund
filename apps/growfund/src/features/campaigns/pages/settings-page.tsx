import { __ } from '@wordpress/i18n';
import { useFormContext } from 'react-hook-form';

import ElementWrapper from '@/components/element-wrapper';
import { TextField } from '@/components/form/text-field';
import { TextareaField } from '@/components/form/textarea-field';
import { Container } from '@/components/layouts/container';
import CampaignFaqsFallbacks from '@/components/pro-fallbacks/campaign/campaign-faqs-fallbacks';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { InfoTooltip } from '@/components/ui/tooltip';
import { useAppConfig } from '@/contexts/app-config';
import StepNavigation from '@/features/campaigns/components/step-navigation';
import { type CampaignForm } from '@/features/campaigns/schemas/campaign';
import { registry } from '@/lib/registry';

const SettingsStep = () => {
  const form = useFormContext<CampaignForm>();
  const { isDonationMode } = useAppConfig();

  const CampaignFaqs = registry.get('CampaignFaqs');

  return (
    <Container size="xs" className="growfund-space-y-4">
      <Card>
        <CardHeader>
          <CardTitle className="growfund-flex growfund-items-center growfund-gap-1">
            {isDonationMode
              ? __('Donation Confirmation Message', 'growfund')
              : __('Pledge Confirmation Message', 'growfund')}
            <InfoTooltip>
              {isDonationMode
                ? __(
                    'This message will be displayed to donors after they complete their donation. Customize the title and description to thank your supporters and provide important next steps.',
                    'growfund',
                  )
                : __(
                    'This message will be displayed to backers after they complete their pledge. Customize the title and description to thank your supporters and provide important next steps.',
                    'growfund',
                  )}
            </InfoTooltip>
          </CardTitle>
        </CardHeader>
        <Separator />
        <CardContent className="growfund-mt-4 growfund-space-y-4">
          <TextField
            control={form.control}
            name="confirmation_title"
            label={__('Title', 'growfund')}
            placeholder={__('Confirmation Title', 'growfund')}
          />
          <TextareaField
            control={form.control}
            name="confirmation_description"
            label={__('Description', 'growfund')}
            placeholder={__('Confirmation Description', 'growfund')}
            rows={5}
          />
        </CardContent>
      </Card>

      <ElementWrapper fallback={<CampaignFaqsFallbacks />}>
        {CampaignFaqs && <CampaignFaqs />}
      </ElementWrapper>

      <div className="growfund-flex growfund-mt-4 growfund-justify-end">
        <StepNavigation />
      </div>
    </Container>
  );
};

export default SettingsStep;
