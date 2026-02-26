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
import { type Donor, type DonorForm, DonorFormSchema } from '@/features/donors/schemas/donor';
import { useCreateDonorMutation, useUpdateDonorMutation } from '@/features/donors/services/donor';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useDialogCloseMiddleware } from '@/hooks/use-wp-media';
import { isDefined } from '@/utils';
interface ManageDonorDialogProps extends React.PropsWithChildren {
  isOpen: boolean;
  onOpenChange: (isOpen: boolean) => void;
  defaultValues?: Donor;
  onSaveChanges?: (donor: Donor) => void;
}

const ManageDonorDialog = ({
  children,
  defaultValues,
  isOpen,
  onOpenChange,
  onSaveChanges,
}: ManageDonorDialogProps) => {
  const formRef = useRef<HTMLFormElement>(null);
  const isEditMode = isDefined(defaultValues);

  const form = useForm<DonorForm>({
    resolver: zodResolver(DonorFormSchema),
  });

  const createDonorMutation = useCreateDonorMutation();
  const updateDonorMutation = useUpdateDonorMutation();

  const { createErrorHandler } = useFormErrorHandler(form);
  const { applyMiddleware } = useDialogCloseMiddleware();

  useEffect(() => {
    if (isDefined(defaultValues)) {
      form.reset.call(null, defaultValues);
    }
  }, [defaultValues, form.reset]);

  return (
    <Dialog open={isOpen} onOpenChange={applyMiddleware(onOpenChange)}>
      <DialogTrigger asChild>{children}</DialogTrigger>
      <DialogContent className="growfund-max-w-[52rem]" tabIndex={undefined}>
        <DialogHeader className="growfund-max-h-[3.75rem]">
          <DialogTitle className="growfund-flex growfund-items-center growfund-gap-2">
            <FileEdit className="growfund-size-5 growfund-text-icon-primary" />
            {isEditMode ? __('Edit Donor', 'growfund') : __('Create Donor', 'growfund')}
          </DialogTitle>
          <DialogCloseButton />
        </DialogHeader>
        <ScrollArea>
          <Container size="xs" className="growfund-mt-3 growfund-max-h-[calc(100svh-280px)]">
            <UserForm
              ref={formRef}
              form={form}
              onSubmit={(values) => {
                if (isEditMode) {
                  const donor = {
                    ...values,
                    id: defaultValues.id,
                  };
                  updateDonorMutation.mutate(donor, {
                    onError: createErrorHandler(),
                    onSuccess() {
                      onSaveChanges?.(donor as Donor);
                      onOpenChange(false);
                      form.reset();
                    },
                  });
                  return;
                }
                createDonorMutation.mutate(values, {
                  onError: createErrorHandler(),
                  onSuccess(response: AxiosResponse<{ id: string }>) {
                    onSaveChanges?.({ ...values, id: response.data.id } as Donor);
                    onOpenChange(false);
                    form.reset();
                  },
                });
              }}
            >
              <UserFormCard>
                <UserFormCardHeader />
                <UserFormCardContent>
                  <UserFormBasics />
                  <UserFormPassword isEditMode={isEditMode} />
                  <UserFormAddress keyPrefix="billing_address" label={__('Address', 'growfund')} />
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
            loading={createDonorMutation.isPending || updateDonorMutation.isPending}
            disabled={createDonorMutation.isPending || updateDonorMutation.isPending}
          >
            {__('Save', 'growfund')}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  );
};
export const ManageDonorDialogTrigger = ({ children }: React.PropsWithChildren) => {
  return <DialogTrigger asChild>{children}</DialogTrigger>;
};

ManageDonorDialog.displayName = 'ManageDonorDialog';
ManageDonorDialogTrigger.displayName = 'ManageDonorDialogTrigger';

export default ManageDonorDialog;
