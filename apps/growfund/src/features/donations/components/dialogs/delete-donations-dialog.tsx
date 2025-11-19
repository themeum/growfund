import { __, sprintf } from '@wordpress/i18n';
import React from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogCloseButton,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useCurrency } from '@/hooks/use-currency';

interface DeleteDonationsDialogProps extends React.PropsWithChildren {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  selectedRows?: {
    id: string;
    amount: number;
    donor_name: string;
  }[];
}

const DeleteDonationsDialog = ({
  children,
  open,
  onOpenChange,
  selectedRows = [],
}: DeleteDonationsDialogProps) => {
  const { toCurrency } = useCurrency();
  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{__('Move to Trash', 'growfund')}</DialogTitle>
          <DialogCloseButton />
        </DialogHeader>

        <div className="growfund-px-4 growfund-max-h-[360px] growfund-overflow-y-auto growfund-rounded">
          {selectedRows.length > 0 ? (
            <>
              <p className="growfund-text-fg-secondary">
                {__(
                  'Move the following donations to trash? You can restore them anytime if needed.',
                  'growfund',
                )}
              </p>
              <div className="growfund-bg-background-white growfund-mt-3 growfund-border growfund-border-border growfund-rounded">
                {selectedRows.map((row) => {
                  return (
                    <div
                      key={row.id}
                      className="growfund-mb-2 growfund-flex growfund-items-center growfund-gap-2 growfund-h-10 growfund-border-b growfund-border-border-tertiary last:growfund-border-b-0 growfund-pl-6 growfund-py-3"
                    >
                      <span className="growfund-w-[156px] growfund-text-fg-subdued growfund-typo-tiny">
                        {/* translators: %s: donation id */}
                        {sprintf(__('ID #%s', 'growfund'), row.id)}
                      </span>
                      <span className="growfund-w-[156px] growfund-text-fg-primary growfund-typo-tiny">
                        {toCurrency(row.amount)}
                      </span>
                      <p className="growfund-w-[156px] growfund-text-fg-primary growfund-typo-tiny">
                        <span className="growfund-typo-tiny growfund-text-fg-secondary">
                          {__('by ', 'growfund')}
                        </span>
                        {row.donor_name}
                      </p>
                    </div>
                  );
                })}
              </div>
            </>
          ) : (
            <span>{__('No donations selected for deletion.', 'growfund')}</span>
          )}
        </div>

        <DialogFooter>
          <Button
            variant="outline"
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Cancel', 'growfund')}
          </Button>
          <Button
            variant="destructive"
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Move to Trash', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

export default DeleteDonationsDialog;

DeleteDonationsDialog.displayName = 'DeleteDonationsDialog';
