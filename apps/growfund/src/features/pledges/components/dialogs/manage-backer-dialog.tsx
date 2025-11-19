import { zodResolver } from '@hookform/resolvers/zod';
import { __ } from '@wordpress/i18n';
import { type AxiosResponse } from 'axios';
import { FileEdit } from 'lucide-react';
import React, { useEffect, useRef } from 'react';
import { useForm } from 'react-hook-form';

import { Container } from '@/components/layouts/container';
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
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    UserForm,
    UserFormAddress,
    UserFormBasics,
    UserFormCard,
    UserFormCardContent,
    UserFormCardHeader,
    UserFormPassword,
} from '@/components/user-form/user-form';
import {
    type Backer,
    type BackerForm,
    BackerFormSchema,
    type BackerInfo,
} from '@/features/backers/schemas/backer';
import {
    useCreateBackerMutation,
    useUpdateBackerMutation,
} from '@/features/backers/services/backer';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { getDefaults } from '@/lib/zod';
import { isDefined } from '@/utils';
interface ManageBackerDialogProps extends React.PropsWithChildren {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  defaultValues?: BackerInfo;
  onSaveChanges?: (backer: BackerInfo) => void;
}

const ManageBackerDialog = ({
  children,
  defaultValues,
  open,
  onOpenChange,
  onSaveChanges,
}: ManageBackerDialogProps) => {
  const formRef = useRef<HTMLFormElement>(null);

  const form = useForm<BackerForm>({
    resolver: zodResolver(BackerFormSchema),
    defaultValues: getDefaults(BackerFormSchema._def.schema),
  });

  const createBackerMutation = useCreateBackerMutation();
  const updateBackerMutation = useUpdateBackerMutation();

  const { createErrorHandler } = useFormErrorHandler(form);
  const { applyMiddleware } = useDialogCloseMiddleware();

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [defaultValues, form.reset]);

  const isEditMode = isDefined(defaultValues);

  return (
    <Dialog open={open} onOpenChange={applyMiddleware(onOpenChange)}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[70rem]" tabIndex={undefined}>
        <DialogHeader className="growfund-max-h-[3.75rem]">
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <FileEdit className="growfund-size-5 growfund-text-icon-primary" />
            {isEditMode ? __('Edit Backer', 'growfund') : __('Create New Backer', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>
        <ScrollArea>
          <Container size="xs" className="growfund-mt-3 growfund-max-h-[calc(100svh-17.5rem)]">
            <UserForm
              ref={formRef}
              form={form}
              onSubmit={(values) => {
                if (isEditMode) {
                  const backer = {
                    ...values,
                    id: defaultValues.id,
                  };
                  updateBackerMutation.mutate(backer, {
                    onError: createErrorHandler(),
                    onSuccess() {
                      onSaveChanges?.(backer as Backer);
                      form.reset();
                      onOpenChange(false);
                    },
                  });

                  return;
                }
                createBackerMutation.mutate(values, {
                  onError: createErrorHandler(),
                  onSuccess(data: AxiosResponse<{ id: string }>) {
                    onSaveChanges?.({ ...values, id: data.data.id } as Backer);
                    form.reset();
                    onOpenChange(false);
                  },
                });
              }}
            >
              <UserFormCard>
                <UserFormCardHeader />
                <UserFormCardContent>
                  <UserFormBasics />
                  <UserFormPassword isEditMode={isEditMode} />
                  <UserFormAddress
                    keyPrefix="shipping_address"
                    label={__('Shipping Address', 'growfund')}
                  />
                  <UserFormAddress
                    keyPrefix="billing_address"
                    isBillingAddress
                    label={__('Billing Address', 'growfund')}
                  />
                </UserFormCardContent>
              </UserFormCard>
            </UserForm>
          </Container>
        </ScrollArea>
        <DialogFooter className="growfund-z-highest">
          <Button
            variant="outline"
            onClick={() => {
              onOpenChange(false);
            }}
          >
            {__('Cancel', 'growfund')}
          </Button>
          <Button
            onClick={() => {
              formRef.current?.requestSubmit();
            }}
            loading={createBackerMutation.isPending || updateBackerMutation.isPending}
            disabled={createBackerMutation.isPending || updateBackerMutation.isPending}
          >
            {__('Save', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};

ManageBackerDialog.displayName = 'ManageBackerDialog';

export default ManageBackerDialog;
