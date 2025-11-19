import { __ } from '@wordpress/i18n';

import ProBanner from '@/components/pro-fallbacks/pro-banner';
import { processSrc } from '@/components/ui/image';

const CampaignDetailsFallback = () => {
  return (
    <div
      className="growfund-relative growfund-min-h-[28rem] growfund-bg-no-repeat growfund-bg-cover"
      style={{
        backgroundImage: `url('${processSrc('/images/campaign-overview-overlay.webp')}')`,
      }}
    >
      <div className="growfund-z-10 growfund-flex growfund-items-center growfund-justify-center growfund-min-h-[28rem]">
        <ProBanner
          title={__('Turn insights into growth', 'growfund')}
          description={__(
            'Pro gives you deeper campaign overview to help you maximize performance and scale faster.',
            'growfund',
          )}
        />
      </div>
    </div>
  );
};

export default CampaignDetailsFallback;
