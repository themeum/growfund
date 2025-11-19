import { __ } from '@wordpress/i18n';

import { SubmittedForReviewIcon } from '@/app/icons';
import { Box, BoxContent } from '@/components/ui/box';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

interface CampaignSubmittedForReviewDialogProps {
  open: boolean;
  onOpenChange: (value: boolean) => void;
}

const CampaignSubmittedForReviewDialog = ({
  open,
  onOpenChange,
}: CampaignSubmittedForReviewDialogProps) => {
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogContent className="growfund-bg-transparent growfund-border-none growfund-max-w-[30rem]">
        <DialogHeader className="growfund-sr-only">
          <DialogTitle className="growfund-sr-only">
            {__('The campaign is submitted for review', 'growfund')}
          </DialogTitle>
        </DialogHeader>
        <DialogCloseButton className="growfund-absolute growfund-right-2 growfund-top-4" />
        <Box className="growfund-rounded-xl">
          <BoxContent className="growfund-flex growfund-flex-col growfund-gap-4 growfund-items-center growfund-p-12">
            <SubmittedForReviewIcon />
            <div className="growfund-space-y-2 growfund-text-center">
              <h4 className="growfund-typo-h4 growfund-font-semibold growfund-text-fg-primary">
                {__('The campaign is submitted for review', 'growfund')}
              </h4>
              <p className="growfund-typo-paragraph">
                {__(
                  "Thanks for submitting your campaign! You'll be notified when it's approved and live.",
                  'growfund',
                )}
              </p>
            </div>
          </BoxContent>
        </Box>
      </DialogContent>
    </Dialog>
  );
};

export default CampaignSubmittedForReviewDialog;
