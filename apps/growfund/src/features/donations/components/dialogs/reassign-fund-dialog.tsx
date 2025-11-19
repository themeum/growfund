import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import React, { useMemo } from 'react';
import { useForm } from 'react-hook-form';
import z from 'zod';

import { SelectField } from '@/components/form/select-field';
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
import { Form } from '@/components/ui/form';
import { useQueryHook } from '@/lib/query-registry';
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';

interface Data {
  id: string;
}

interface ReassignFundDialogProps extends React.PropsWithChildren {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  data: Data[];
  onReassign: (closeDialog: () => void, fundId: string) => void;
  loading?: boolean;
}

const ReassignFundSchema = z.object({
  fund_id: z.string({
    message: 'Fund is required',
  }),
});

const ReassignFundDialog = ({
  children,
  open,
  onOpenChange,
  onReassign,
  loading = false,
}: ReassignFundDialogProps) => {
  const form = useForm<z.infer<typeof ReassignFundSchema>>({
    resolver: zodResolver(ReassignFundSchema),
    defaultValues: getDefaults(ReassignFundSchema),
  });

  const useAllFundsQuery = useQueryHook<undefined, Record<string, unknown>[]>('useAllFundsQuery');

  const fundsQuery = useAllFundsQuery?.();

  const fundOptions = useMemo(() => {
    if (!isDefined(fundsQuery?.data)) return [];

    return fundsQuery.data.map((fund) => ({
      value: fund.id as string,
      label: fund.title as string,
    }));
  }, [fundsQuery?.data]);

  return (
    <Dialog open={open} onOpenChange={onOpenChange}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{__('Reassign donations to fund', 'growfund')}</DialogTitle>
          <DialogCloseButton />
        </DialogHeader>
        <Form {...form}>
          <div className="growfund-px-4">
            <SelectField
              control={form.control}
              label={__(
                'Revenue from the selected donations will be reassigned to this fund:',
                'growfund',
              )}
              name="fund_id"
              options={fundOptions}
              placeholder={__('Select a fund', 'growfund')}
              className="growfund-bg-background-surface"
            />
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
              loading={loading}
              onClick={() => {
                const values = form.getValues();
                onReassign(() => {
                  onOpenChange(false);
                }, values.fund_id);
              }}
            >
              {__('Reassign', 'growfund')}
            </Button>
          </DialogFooter>
        </Form>
      </DialogContent>
    </Dialog>
  );
};

export default ReassignFundDialog;

ReassignFundDialog.displayName = 'ReassignFundDialog';
