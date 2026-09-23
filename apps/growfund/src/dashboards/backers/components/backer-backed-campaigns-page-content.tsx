import { __, sprintf } from '@wordpress/i18n';
import { HelpingHand } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';

import { DatePickerField } from '@/components/form/date-picker-field';
import { TextField } from '@/components/form/text-field';
import { Container } from '@/components/layouts/container';
import { Form } from '@/components/ui/form';
import BackedCampaignList from '@/dashboards/backers/features/backed-campaigns/backed-campaign-list';
import { useDashboardLayoutContext } from '@/dashboards/shared/contexts/root-layout-context';

const BackerBackedCampaignsPageContent = () => {
  const { setTopbar } = useDashboardLayoutContext();
  const [totalCampaigns, setTotalCampaigns] = useState(0);

  const form = useForm<{ search: string; date_range: { from?: Date; to?: Date } }>();

  useEffect(() => {
    setTopbar({
      title: __('Backed Campaigns', 'growfund'),
      icon: HelpingHand,
    });
  }, [setTopbar]);

  return (
    <Container className="growfund-mt-6 sm:growfund-mt-10 growfund-space-y-3" size="sm">
      <Form {...form}>
        <div className="growfund-flex growfund-flex-col sm:growfund-flex-row sm:growfund-items-center sm:growfund-justify-between growfund-gap-4">
          <div className="growfund-flex growfund-flex-wrap growfund-items-center growfund-gap-2 growfund-text-fg-primary">
            <h5 className="growfund-typo-h6 sm:growfund-typo-h5 growfund-font-semibold">
              {__('Backed Campaigns', 'growfund')}
            </h5>

            <h6 className="growfund-typo-small sm:growfund-typo-h6 growfund-font-regular">
              {sprintf('(%s)', totalCampaigns)}
            </h6>
          </div>

          <div className="growfund-w-full sm:growfund-w-auto sm:growfund-max-w-[25rem] growfund-flex growfund-flex-col sm:growfund-flex-row growfund-items-stretch sm:growfund-items-center growfund-gap-2">
            <TextField
              control={form.control}
              name="search"
              type="search"
              placeholder={__('Search...', 'growfund')}
            />

            <DatePickerField
              control={form.control}
              name="date_range"
              type="range"
              placeholder={__('Select Date', 'growfund')}
              showRangePresets
              clearable
            />
          </div>
        </div>

        <BackedCampaignList onLoadData={setTotalCampaigns} />
      </Form>
    </Container>
  );
};

export default BackerBackedCampaignsPageContent;
