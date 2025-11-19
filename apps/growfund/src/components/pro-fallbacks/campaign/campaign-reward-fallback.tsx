import { DialogTitle } from '@radix-ui/react-dialog';

import { GiftPackIcon, ItemsColorful } from '@/app/icons';
import ProBanner from '@/components/pro-fallbacks/pro-banner';
import { Dialog, DialogContent, DialogTrigger } from '@/components/ui/dialog';

const CampaignRewardFallback = ({
  children,
  title,
  description,
  isRewardItem = false,
}: React.PropsWithChildren<{ title: string; description: string; isRewardItem?: boolean }>) => {
  return (
    <Dialog>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-bg-background-surface">
        <DialogTitle className="growfund-sr-only"></DialogTitle>
        <div className="growfund-flex growfund-justify-center growfund-pt-8">
          {!isRewardItem ? <GiftPackIcon /> : <ItemsColorful />}
        </div>
        <ProBanner title={title} description={description} className="growfund-pt-0" />
      </DialogContent>
    </Dialog>
  );
};

export default CampaignRewardFallback;
