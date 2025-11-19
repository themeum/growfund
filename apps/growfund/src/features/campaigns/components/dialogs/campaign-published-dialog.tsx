import { __ } from '@wordpress/i18n';
import { Check, Copy } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import {
    FacebookSocialIcon,
    LinkedinSocialIcon,
    RocketIcon,
    TelegramSocialIcon,
    TwitterXSocialIcon,
    WhatsappSocialIcon,
} from '@/app/icons';
import { Box, BoxContent } from '@/components/ui/box';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { copyToClipboard } from '@/utils';
import { createContentSharer } from '@/utils/share';

interface CampaignPublishedDialogProps {
  open: boolean;
  onOpenChange: (value: boolean) => void;
  url: string;
}

const CampaignPublishedDialog = ({ open, onOpenChange, url }: CampaignPublishedDialogProps) => {
  const [isCopied, setIsCopied] = useState(false);
  const { shareOn } = createContentSharer(url);

  useEffect(() => {
    if (!isCopied) {
      return;
    }

    const timeout = setTimeout(() => {
      setIsCopied(false);
    }, 3000);
    return () => {
      clearTimeout(timeout);
    };
  }, [isCopied]);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="growfund-bg-transparent growfund-border-none growfund-max-w-[26.75rem]">
        <DialogHeader className="growfund-sr-only">
          <DialogTitle className="growfund-sr-only">
            {__('Your campaign is published!', 'growfund')}
          </DialogTitle>
        </DialogHeader>
        <div className="growfund-space-y-2 growfund-relative">
          <DialogCloseButton className="growfund-absolute growfund-right-2 growfund-top-4" />
          <Box className="growfund-rounded-xl">
            <BoxContent className="growfund-flex growfund-flex-col growfund-items-center growfund-p-6">
              <RocketIcon />
              <h4 className="growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary">
                {__('Your campaign is published!', 'growfund')}
              </h4>
            </BoxContent>
          </Box>
          <Box className="growfund-rounded-xl">
            <BoxContent className="growfund-p-6 growfund-space-y-4">
              <div className="growfund-grid growfund-grid-cols-[3fr_1fr] growfund-border growfund-border-border growfund-rounded-lg">
                <div className="growfund-grid growfund-gap-1 growfund-px-3 growfund-py-1">
                  <span className="growfund-typo-tiny growfund-text-fg-subdued">
                    {__('Quick link', 'growfund')}
                  </span>
                  <span className="growfund-typo-small growfund-text-fg-primary growfund-truncate">{url}</span>
                </div>
                <div className="growfund-border-l growfund-border-l-border growfund-flex growfund-items-center growfund-justify-center growfund-py-1 growfund-bg-background-fill-secondary">
                  <Button
                    variant="link"
                    className="hover:growfund-no-underline growfund-w-32"
                    onClick={async () => {
                      const isCopied = await copyToClipboard(url);
                      if (isCopied) {
                        setIsCopied(true);
                      } else {
                        toast.error(__('Failed to copy link. Please copy manually.', 'growfund'));
                      }
                    }}
                  >
                    {!isCopied ? (
                      <>
                        <Copy />
                        {__('Copy link', 'growfund')}
                      </>
                    ) : (
                      <>
                        <Check className="growfund-text-icon-success" />
                        <span className="growfund-text-fg-success">{__('Copied', 'growfund')}</span>
                      </>
                    )}
                  </Button>
                </div>
              </div>

              <div className="growfund-space-y-2">
                <h6 className="growfund-typo-h6 growfund-font-semibold growfund-text-fg-primary">
                  {__('Reach more contributors by sharing!', 'growfund')}
                </h6>
                <p className="growfund-typo-tiny growfund-text-fg-secondary">
                  {__(
                    'Spread the word about your campaign! The more people who are aware, the greater the opportunities for success.',
                    'growfund',
                  )}
                </p>
              </div>

              <div className="growfund-grid growfund-grid-cols-2">
                <div className="growfund-flex growfund-flex-col growfund-gap-2 [&>button]:growfund-justify-start">
                  <Button
                    variant="ghost"
                    onClick={() => {
                      shareOn('facebook');
                    }}
                  >
                    <FacebookSocialIcon />
                    {__('Facebook', 'growfund')}
                  </Button>
                  <Button
                    variant="ghost"
                    onClick={() => {
                      shareOn('whatsapp');
                    }}
                  >
                    <WhatsappSocialIcon />
                    {__('WhatsApp', 'growfund')}
                  </Button>
                  <Button
                    variant="ghost"
                    onClick={() => {
                      shareOn('telegram');
                    }}
                  >
                    <TelegramSocialIcon />
                    {__('Telegram', 'growfund')}
                  </Button>
                </div>
                <div className="growfund-flex growfund-flex-col growfund-gap-2 [&>button]:growfund-justify-start">
                  <Button
                    variant="ghost"
                    onClick={() => {
                      shareOn('linkedin');
                    }}
                  >
                    <LinkedinSocialIcon />
                    {__('LinkedIn', 'growfund')}
                  </Button>
                  <Button
                    variant="ghost"
                    onClick={() => {
                      shareOn('twitter');
                    }}
                  >
                    <TwitterXSocialIcon />
                    {__('X', 'growfund')}
                  </Button>
                </div>
              </div>
            </BoxContent>
          </Box>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default CampaignPublishedDialog;
