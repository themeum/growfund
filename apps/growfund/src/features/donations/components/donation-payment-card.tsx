import { __, sprintf } from '@wordpress/i18n';
import { useCallback, useEffect, useMemo } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';

import { TextField } from '@/components/form/text-field';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { useAppConfig } from '@/contexts/app-config';
import { CampaignProvider } from '@/features/campaigns/contexts/campaign-context';
import { type Campaign } from '@/features/campaigns/schemas/campaign';
import { type DonationForm } from '@/features/donations/schemas/donation-form';
import { AppConfigKeys } from '@/features/settings/context/settings-context';
import { useCurrency } from '@/hooks/use-currency';
import useCurrentUser from '@/hooks/use-current-user';
import { registry } from '@/lib/registry';
import { cn } from '@/lib/utils';
import { isDefined } from '@/utils';

interface DonationPaymentCardProps {
  campaign: Campaign;
}

const DonationPaymentCard = ({ campaign }: DonationPaymentCardProps) => {
  const { appConfig } = useAppConfig();
  const { isAdmin, isFundraiser } = useCurrentUser();
  const form = useFormContext<DonationForm>();
  const { toCurrency } = useCurrency();

  const presetAmounts = useMemo(() => {
    if (!isDefined(campaign) || !isDefined(campaign.suggested_options)) return [];

    return campaign.suggested_options;
  }, [campaign]);

  const defaultPresetAmount = useMemo(() => {
    return presetAmounts.find((option) => option.is_default);
  }, [presetAmounts]);

  const getDonationAmountDescription = () => {
    if (!isDefined(campaign) || !campaign.allow_custom_donation) return;

    if (campaign.min_donation_amount && campaign.max_donation_amount) {
      return sprintf(
        /* translators: 1: min donation amount, 2: max donation amount */
        __('Enter any amount between %1$s and %2$s', 'growfund'),
        toCurrency(campaign.min_donation_amount),
        toCurrency(campaign.max_donation_amount),
      );
    }

    if (campaign.min_donation_amount) {
      return sprintf(
        /* translators: %s: min donation amount */
        __('Enter any amount greater than or equal to %s', 'growfund'),
        toCurrency(campaign.min_donation_amount),
      );
    }

    if (campaign.max_donation_amount) {
      return sprintf(
        /* translators: %s: max donation amount */
        __('Enter any amount less than or equal to %s', 'growfund'),
        toCurrency(campaign.max_donation_amount),
      );
    }
  };

  const handlePresetClick = useCallback(
    (value: number) => {
      form.setValue('amount', value);
    },
    [form],
  );

  useEffect(() => {
    if (isDefined(defaultPresetAmount)) {
      handlePresetClick(defaultPresetAmount.amount);
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [defaultPresetAmount]);

  const amount = useWatch({ control: form.control, name: 'amount' });

  const DonationsFundSelection = registry.get('DonationsFundSelection');

  return (
    <Box>
      <BoxContent className="growfund-space-y-5">
        <h6 className="growfund-text-fg-primary growfund-font-semibold growfund-typo-h6">
          {__('Donation', 'growfund')}
        </h6>
        {appConfig[AppConfigKeys.Campaign]?.allow_fund && (
          <CampaignProvider campaign={campaign}>
            {DonationsFundSelection && <DonationsFundSelection />}
          </CampaignProvider>
        )}

        <div className="growfund-space-y-1">
          <span className="growfund-typo-small growfund-font-medium">
            {__('Available Options', 'growfund')}
          </span>
          <div className="growfund-flex growfund-gap-2">
            {presetAmounts.map((item, index) => (
              <Button
                key={index}
                variant="outline"
                className={cn(
                  'growfund-typo-small growfund-font-medium',
                  item.amount === amount && 'growfund-border growfund-border-border-inverse',
                )}
                onClick={() => {
                  handlePresetClick(item.amount);
                }}
                title={item.description ?? ''}
              >
                {toCurrency(item.amount)}
              </Button>
            ))}
          </div>
        </div>

        <TextField
          control={form.control}
          type="number"
          name="amount"
          label={__('Amount', 'growfund')}
          placeholder="0"
          disabled={!campaign.allow_custom_donation && !(isAdmin || isFundraiser)}
          description={getDonationAmountDescription()}
        />
      </BoxContent>
    </Box>
  );
};

export default DonationPaymentCard;
