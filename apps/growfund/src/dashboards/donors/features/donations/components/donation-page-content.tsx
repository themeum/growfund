import { __, sprintf } from '@wordpress/i18n';
import { useState } from 'react';
import { useForm } from 'react-hook-form';

import { DatePickerField } from '@/components/form/date-picker-field';
import { SelectField } from '@/components/form/select-field';
import { TextField } from '@/components/form/text-field';
import { Form } from '@/components/ui/form';
import DonationList from '@/dashboards/donors/features/donations/components/donation-list';

const DonationPageContent = () => {
  const [queryMeta, setQueryMeta] = useState({ total: 0, overall: 0 });
  const form = useForm<{
    search: string;
    status: string;
    date_range?: { from?: Date; to?: Date };
  }>();

  return (
    <Form {...form}>
      {queryMeta.overall > 0 && (
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h4 className="growfund-typo-h5 growfund-font-semibold growfund-text-primary">
            {/* translators: %d: number of donations */}
            {sprintf(__('Donations (%d)', 'growfund'), queryMeta.total)}
          </h4>
          <div className="growfund-flex growfund-items-center growfund-gap-2">
            <TextField
              className="growfund-bg-background-surface"
              control={form.control}
              type="search"
              name="search"
              placeholder={__('Search...', 'growfund')}
            />
            <SelectField
              control={form.control}
              className="growfund-bg-background-fill growfund-rounded-md"
              name="status"
              placeholder={__('All Donations', 'growfund')}
              options={[
                { value: 'all', label: __('All Donations', 'growfund') },
                { value: 'pending', label: __('Pending', 'growfund') },
                { value: 'completed', label: __('Completed', 'growfund') },
                { value: 'cancelled', label: __('Cancelled', 'growfund') },
                { value: 'failed', label: __('Failed', 'growfund') },
              ]}
            />
            <DatePickerField
              control={form.control}
              name="date_range"
              placeholder={__('Pick a Date', 'growfund')}
              type="range"
              showRangePresets
              clearable
            />
          </div>
        </div>
      )}
      <DonationList onLoadData={setQueryMeta} />
    </Form>
  );
};

export default DonationPageContent;
