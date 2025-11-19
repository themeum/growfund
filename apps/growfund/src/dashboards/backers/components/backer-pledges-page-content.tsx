import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';
import { useForm } from 'react-hook-form';

import { DatePickerField } from '@/components/form/date-picker-field';
import { TextField } from '@/components/form/text-field';
import { Form } from '@/components/ui/form';
import PledgesPageContent from '@/dashboards/backers/features/pledges/components/pledges-page-content';

export interface FilterForm {
  search: string;
  date_range?: {
    from?: Date;
    to?: Date;
  };
}

const BackerPledgesPageContent = () => {
  const [totalPledges, setTotalPledges] = useState(0);
  const form = useForm<FilterForm>();

  return (
    <Form {...form}>
      <div className="growfund-flex growfund-items-center growfund-justify-between growfund-mb-3">
        <div className="growfund-flex growfund-items-center growfund-gap-1">
          <h5 className="growfund-typo-h5 growfund-font-bold">{__('All Pledges', 'growfund')}</h5>
          <h6 className="growfund-typo-h6 growfund-font-bold">{sprintf('(%s)', totalPledges)}</h6>
        </div>

        <div className="growfund-max-w-[25rem] growfund-flex growfund-items-center growfund-gap-2">
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
      <PledgesPageContent onLoadData={setTotalPledges} />
    </Form>
  );
};

export default BackerPledgesPageContent;
