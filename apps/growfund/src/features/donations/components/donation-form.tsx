import { useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import CampaignCard from '@/components/campaigns/campaign-card';
import { Container } from '@/components/layouts/container';
import { Form } from '@/components/ui/form';
import { useAppConfig } from '@/contexts/app-config';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import DonationNotesCard from '@/features/donations/components/donation-notes-card';
import DonationPaymentCard from '@/features/donations/components/donation-payment-card';
import DonorSelectionCard from '@/features/donations/components/donor-selection-card';
import PaymentView from '@/features/donations/components/payment-view';
import TributeView from '@/features/donations/components/tribute-view';
import { type DonationForm } from '@/features/donations/schemas/donation-form';
import AddCampaignEmptyState from '@/features/pledges/components/campaigns/empty-state';
import PaymentMethodCard from '@/features/pledges/components/payment-method-card';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { isDefined } from '@/utils';

const DonationForm = () => {
  const { appConfig } = useAppConfig();
  const form = useFormContext<DonationForm>();
  const notes = useWatch({ control: form.control, name: 'notes' });
  const amount = useWatch({ control: form.control, name: 'amount' });
  const status = useWatch({ control: form.control, name: 'status' });
  const [campaign, setCampaign] = useState<Campaign | null>(null);

  return (
    <Container className="growfund-py-10">
      <Form {...form}>
        <div className="growfund-grid growfund-grid-cols-[auto_20rem] growfund-gap-4">
          <div className="growfund-space-y-4">
            {isDefined(campaign) ? (
              <>
                <CampaignCard
                  campaign={campaign}
                  onRemove={() => {
                    form.setValue('campaign_id', '');
                    setCampaign(null);
                  }}
                />

                <DonationPaymentCard campaign={campaign} />
                <PaymentView amount={amount ?? 0} donationStatus={status} />
              </>
            ) : (
              <div>
                <AddCampaignEmptyState
                  onSelectCampaign={(campaign) => {
                    setCampaign(campaign);
                  }}
                />
              </div>
            )}
          </div>
          <div className="growfund-space-y-4">
            <PaymentMethodCard form={form} />
            <DonorSelectionCard />

            {appConfig[AppConfigKeys.Campaign]?.allow_tribute && campaign?.has_tribute && (
              <TributeView campaign={campaign} />
            )}

            <DonationNotesCard
              value={notes}
              onChange={(value) => {
                form.setValue('notes', value);
              }}
            />
          </div>
        </div>
      </Form>
    </Container>
  );
};

export default DonationForm;
