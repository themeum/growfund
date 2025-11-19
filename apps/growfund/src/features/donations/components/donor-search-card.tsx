import { __ } from '@wordpress/i18n';

import { Box, BoxContent } from '@/components/ui/box';
import { Input } from '@/components/ui/input';

const DonorSearchCard = () => {
  return (
    <Box className="">
      <BoxContent>
        <div className="growfund-flex growfund-items-center growfund-justify-between growfund-min-h-9">
          <h6 className="growfund-typo-h6 growfund-font-medium growfund-text-fg-primary">
            {__('Donor', 'growfund')}
          </h6>
        </div>
        <Input type="text" placeholder={__('Search Donor', 'growfund')} />
      </BoxContent>
    </Box>
  );
};

export default DonorSearchCard;
