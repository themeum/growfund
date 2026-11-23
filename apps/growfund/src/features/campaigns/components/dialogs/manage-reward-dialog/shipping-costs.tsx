import { __ } from '@wordpress/i18n';
import { Plus, Trash2 } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import { useFieldArray, useFormContext, useWatch } from 'react-hook-form';

import { ComboBoxField } from '@/components/form/combobox-field';
import { TextField } from '@/components/form/text-field';
import { Box } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { ShippingRestOfTheWorld } from '@/config/price-calculator';
import { useCampaignReward } from '@/features/campaigns/contexts/campaign-reward';
import { type RewardForm } from '@/features/campaigns/schemas/reward';
import { useCurrency } from '@/hooks/use-currency';
import { cn } from '@/lib/utils';
import { countriesAsOptions } from '@/utils/countries';

const ShippingCosts = () => {
  const form = useFormContext<RewardForm>();
  const { rewards } = useCampaignReward();
  const shippingCosts = useWatch({ control: form.control, name: 'shipping_costs' }) ?? [];

  const firstRewardShippingCost = useMemo(() => {
    if (rewards.length === 0) {
      return null;
    }

    return rewards[0].shipping_costs;
  }, [rewards]);

  useEffect(() => {
    if (shippingCosts.length === 0 && firstRewardShippingCost) {
      form.setValue('shipping_costs', firstRewardShippingCost);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [firstRewardShippingCost, form.setValue]);

  const fieldArray = useFieldArray({
    control: form.control,
    name: 'shipping_costs',
  });

  const { toCurrency } = useCurrency();

  const fields = fieldArray.fields.map((field, index) => {
    return {
      ...field,
      ...shippingCosts[index],
    };
  });

  const shippingErrors = form.getFieldState('shipping_costs').error;

  return (
    <div className="growfund-space-y-2">
      <Box
        className={cn(
          !!shippingErrors &&
            'growfund-border-border-critical growfund-bg-background-fill-critical-secondary',
        )}
      >
        <Table wrapperClassname="growfund-overflow-visible">
          <TableHeader>
            <TableRow className="growfund-hidden sm:growfund-table-row">
              <TableHead className="growfund-border-r growfund-border-r-border">
                {__('Location', 'growfund')}
              </TableHead>
              <TableHead>{__('Cost', 'growfund')}</TableHead>
              <TableHead />
            </TableRow>
          </TableHeader>
          <TableBody>
            {fields.map((field, index) => {
              return (
                <TableRow
                  key={field.id}
                  className="growfund-flex growfund-flex-col growfund-gap-2 growfund-p-3 growfund-border-b sm:growfund-table-row sm:growfund-p-0"
                >
                  <TableCell className="growfund-border-r-0 sm:growfund-border-r sm:growfund-border-r-border sm:growfund-w-[12.125rem] growfund-p-0 sm:growfund-p-2">
                    <span className="growfund-typo-tiny growfund-text-fg-muted growfund-mb-1 growfund-block sm:growfund-hidden">
                      {__('Location', 'growfund')}
                    </span>
                    <div className="growfund-flex growfund-items-center growfund-gap-2">
                      <ComboBoxField
                        control={form.control}
                        name={`shipping_costs.${index}.location` as 'shipping_costs.0.location'}
                        className="growfund-border-transparent hover:growfund-border-border focus-visible:growfund-border-border growfund-w-full"
                        options={[
                          {
                            label: __('Rest of the world', 'growfund'),
                            value: ShippingRestOfTheWorld,
                            icon: '🌐',
                          },
                          ...countriesAsOptions({ with_flag: true }),
                        ]}
                      />
                    </div>
                  </TableCell>

                  <TableCell className="sm:growfund-w-[12.125rem] growfund-group growfund-p-0 sm:growfund-p-2">
                    <span className="growfund-typo-tiny growfund-text-fg-muted growfund-mb-1 growfund-block sm:growfund-hidden">
                      {__('Cost', 'growfund')}
                    </span>

                    <TextField
                      control={form.control}
                      name={`shipping_costs.${index}.cost` as `shipping_costs.0.cost`}
                      type="number"
                      placeholder={__('e.g. 50.00', 'growfund')}
                      data-name="input"
                      className="growfund-flex sm:growfund-hidden sm:group-hover:growfund-flex"
                    />
                    <span
                      className="growfund-typo-small growfund-text-fg-primary growfund-font-medium growfund-ms-2 growfund-hidden sm:growfund-block sm:group-hover:growfund-hidden"
                      data-name="label"
                    >
                      {field.cost ? toCurrency(field.cost) : __('Free', 'growfund')}
                    </span>
                  </TableCell>

                  <TableCell className="growfund-p-0 sm:growfund-p-2 growfund-flex growfund-justify-end sm:growfund-table-cell">
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => {
                        fieldArray.remove(index);
                      }}
                      className="hover:growfund-text-icon-critical"
                    >
                      <Trash2 className="growfund-text-icon-primary hover:growfund-text-icon-critical" />
                    </Button>
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>

        <div className="growfund-border-t growfund-border-t-border growfund-h-12 growfund-flex growfund-items-center">
          <Button
            variant="ghost"
            className="hover:growfund-bg-transparent hover:growfund-text-fg-brand growfund-w-full sm:growfund-w-auto"
            onClick={() => {
              fieldArray.append({ location: '', cost: 0 });
            }}
          >
            <Plus />
            <span className="growfund-truncate">{__('Add Another Destination', 'growfund')}</span>
          </Button>
        </div>
      </Box>
      {!!shippingErrors && shippingErrors.message?.[0] && (
        <p className="growfund-typo-small growfund-text-fg-critical">{shippingErrors.message[0]}</p>
      )}
    </div>
  );
};

export default ShippingCosts;
