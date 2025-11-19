import { __ } from '@wordpress/i18n';
import { FileText } from 'lucide-react';

import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import { processSrc } from '@/components/ui/image';
import { ProBadge } from '@/components/ui/pro-badge';

const ActivityLogFallback = () => {
  return (
    <Box className="growfund-border-none">
      <BoxContent className="growfund-p-5">
        <div className="growfund-flex growfund-items-center growfund-justify-between">
          <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
            {__('Activity Logs', 'growfund')} <ProBadge />
          </h6>
          <Button
            variant="ghost"
            size="sm"
          >
            <FileText />
            {__('See All Logs', 'growfund')}
          </Button>
        </div>
        <div
          className="growfund-relative growfund-min-h-52 growfund-bg-no-repeat growfund-bg-contain growfund-mt-4"
          style={{
            backgroundImage: `url('${processSrc('/images/activity-log-overlay.webp')}')`,
          }}
        ></div>
      </BoxContent>
    </Box>
  );
};

export default ActivityLogFallback;
