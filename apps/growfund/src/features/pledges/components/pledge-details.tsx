import { __ } from '@wordpress/i18n';
import { useForm } from 'react-hook-form';

import CampaignCard from '@/components/campaigns/campaign-card';
import { Box, BoxContent } from '@/components/ui/box';
import { Form } from '@/components/ui/form';
import UserPreviewCard from '@/components/users/user-preview-card';
import { type Backer } from '@/features/backers/schemas/backer';
import PaymentCard from '@/features/pledges/components/payment-card';
import PledgeAction from '@/features/pledges/components/pledge-action/pledge-action';
import PledgeRewardPreview from '@/features/pledges/components/rewards/pledge-reward-preview';
import ShippingMethodCard from '@/features/pledges/components/shipping-method-card';
import PledgeTimeline from '@/features/pledges/components/timeline/pledge-timeline';
import { type Pledge, type PledgeStatus } from '@/features/pledges/schemas/pledge';
import { isDefined } from '@/utils';

interface PledgeDetailsProps {
  pledge: Pledge;
}

const PledgeDetails = ({ pledge }: PledgeDetailsProps) => {
  const form = useForm<{ status: PledgeStatus }>({
    defaultValues: {
      status: pledge.status,
    },
  });

  return (
    <div className="growfund-grid growfund-grid-cols-[auto_20rem] growfund-gap-4">
      <div>
        <CampaignCard campaign={pledge.campaign} mode="view" />
        {isDefined(pledge.reward) && <PledgeRewardPreview mode="view" reward={pledge.reward} />}

        <div className="growfund-mt-4">
          <PaymentCard
            payment={{
              payment_status: pledge.payment.payment_status,
              amount: pledge.payment.amount,
              shipping_cost: pledge.payment.shipping_cost,
              bonus_support_amount: pledge.payment.bonus_support_amount,
              recovery_fee: pledge.payment.recovery_fee,
              payment_method: pledge.payment.payment_method,
            }}
            pledgeOption={pledge.pledge_option}
          />
        </div>
        <PledgeTimeline pledgeId={pledge.id} />
      </div>
      <Form {...form}>
        <div className="growfund-space-y-4">
          <PledgeAction pledge={pledge} />
          <UserPreviewCard user={pledge.backer as Backer} title={__('Backer', 'growfund')} />
          {isDefined(pledge.reward) && <ShippingMethodCard reward={pledge.reward} deliveryOption={pledge.delivery_option} />}
          <Box>
            <BoxContent>
              <h6 className="growfund-typo-h6 growfund-text-fg-primary">
                {__('Notes', 'growfund')}
              </h6>

              <div className="growfund-typo-small growfund-text-fg-secondary growfund-mt-3">
                {pledge.notes ?? __('No notes', 'growfund')}
              </div>
            </BoxContent>
          </Box>
        </div>
      </Form>
    </div>
  );
};

export default PledgeDetails;
