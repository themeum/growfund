import { __ } from '@wordpress/i18n';

import ProBanner from '@/components/pro-fallbacks/pro-banner';
import { processSrc } from '@/components/ui/image';

const DonationListFallback = () => {
  return (
    <div className="growfund-relative growfund-min-h-[34rem] growfund-bg-no-repeat growfund-bg-contain growfund-bg-center overflow-hidden">
      <div
        className="growfund-absolute growfund-inset-0 growfund-bg-no-repeat growfund-bg-contain growfund-bg-center growfund-min-h-[34rem]"
        style={{
          backgroundImage: `url('${processSrc('/images/table-overlay.webp')}')`,
          opacity: 0.2,
        }}
      ></div>
      <div className="growfund-relative growfund-z-10 growfund-flex growfund-items-center growfund-justify-center growfund-min-h-[34rem]">
        <ProBanner
          title={__('Turn insights into growth', 'growfund')}
          description={__(
            'Pro gives you deeper donor overview to help you maximize performance and scale faster.',
            'growfund',
          )}
        />
      </div>
    </div>
  );
};

export default DonationListFallback;
