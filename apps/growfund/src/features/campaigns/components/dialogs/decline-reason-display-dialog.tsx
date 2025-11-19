import { __ } from '@wordpress/i18n';
import { AlertOctagon } from 'lucide-react';
import React from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { User } from '@/utils/user';

const DeclineReasonDisplayDialog = ({
  reason,
  children,
}: React.PropsWithChildren<{ reason?: string | null }>) => {
  return (
    <Dialog>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-bg-background-white">
        <DialogTitle className="growfund-sr-only">{__('Decline reason', 'growfund')}</DialogTitle>

        <div className="growfund-px-8 growfund-py-6 growfund-space-y-4">
          <div className="growfund-w-full growfund-flex growfund-flex-col growfund-gap-2 growfund-items-center growfund-justify-center growfund-text-center">
            <AlertOctagon className="growfund-size-6 growfund-text-icon-critical" />
            <h5 className="growfund-typo-h5 growfund-text-fg-critical">
              {User.isAdmin()
                ? __('You have declined this campaign', 'growfund')
                : __('Your Campaign is Declined!', 'growfund')}
            </h5>
            <p className="growfund-typo-sm growfund-text-fg-secondary">
              {User.isAdmin()
                ? __('Reason', 'growfund')
                : __(
                    'Your campaign submission has been reviewed and requires some changes before it can be approved.',
                    'growfund',
                  )}
            </p>
          </div>

          {reason && (
            <div
              className="growfund-bg-background-fill-caution-secondary growfund-rounded-sm growfund-p-4 growfund-border-l-4 growfund-border-l-border-warning growfund-typo-small growfund-text-fg-primary"
              dangerouslySetInnerHTML={{ __html: reason }}
            />
          )}
          <div>
            <DialogClose asChild>
              <Button variant="secondary" size="lg" className="growfund-w-full">
                {__('I understand', 'growfund')}
              </Button>
            </DialogClose>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default DeclineReasonDisplayDialog;
