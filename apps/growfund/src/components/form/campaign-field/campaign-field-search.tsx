import { __ } from '@wordpress/i18n';

import { useCampaignFieldContext } from '@/components/form/campaign-field/campaign-field-context';
import { FormControl } from '@/components/ui/form';
import { Input } from '@/components/ui/input';

const CampaignFieldSearch = () => {
  const { search, setSearch } = useCampaignFieldContext();
  return (
    <div className="growfund-flex growfund-justify-between growfund-gap-3 growfund-px-4 growfund-pt-4">
      <FormControl>
        <Input
          placeholder={__('Search for a campaign...', 'growfund')}
          type="search"
          value={search}
          onChange={(event) => {
            setSearch(event.target.value);
          }}
          autoFocus
        />
      </FormControl>
    </div>
  );
};

export default CampaignFieldSearch;
