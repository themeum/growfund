import { __ } from '@wordpress/i18n';
import { type FieldValues, type UseFormReturn } from 'react-hook-form';

import { Container } from '@/components/layouts/container';
import PageTitle from '@/components/layouts/page-title';
import { Button } from '@/components/ui/button';
import {
    FormKeys,
    useUserSettingsContext,
} from '@/dashboards/shared/contexts/user-settings-context';
import {
    type BackerNotificationsPayload,
    type DonorNotificationsPayload,
} from '@/dashboards/shared/schemas/user';
import { type BackerForm } from '@/features/backers/schemas/backer';
import { useUpdateBackerMutation } from '@/features/backers/services/backer';
import { type DonorForm } from '@/features/donors/schemas/donor';
import { useUpdateDonorMutation } from '@/features/donors/services/donor';
import { useFormErrorHandler } from '@/hooks/use-form-error-handler';
import { useUpdateUserNotificationsMutation } from '@/services/user';
import { User as CurrentUser } from '@/utils/user';

const UserSettingsTopbar = () => {
  const { getCurrentForm, getCurrentKey, isCurrentFormDirty, user } = useUserSettingsContext();

  const form = getCurrentForm();
  const key = getCurrentKey();

  const updateUserNotificationsMutation = useUpdateUserNotificationsMutation();
  const updateBackerMutation = useUpdateBackerMutation();
  const updateDonorMutation = useUpdateDonorMutation();

  const { createErrorHandler } = useFormErrorHandler(form);

  const userId = user?.id;

  const isBacker = CurrentUser.isBacker();
  const isDonor = CurrentUser.isDonor();

  if (!isBacker && !isDonor) {
    return null;
  }

  const handleBackerFormSubmit = <T extends FieldValues>(values: T, form: UseFormReturn<T>) => {
    if (!userId) {
      return;
    }

    if (key === FormKeys.Account) {
      const backer = {
        ...(values as unknown as BackerForm),
        id: userId,
      };
      updateBackerMutation.mutate(backer, {
        onError: createErrorHandler(),
        onSuccess() {
          form.reset(values);
        },
      });
      return;
    }

    updateUserNotificationsMutation.mutate(
      {
        ...values,
        id: userId,
      } as unknown as BackerNotificationsPayload,
      {
        onSuccess() {
          form.reset(values);
        },
      },
    );
  };

  const handleDonorFormSubmit = <T extends FieldValues>(values: T, form: UseFormReturn<T>) => {
    if (!userId) {
      return;
    }

    if (key === FormKeys.Account) {
      const donor = {
        ...(values as unknown as DonorForm),
        id: userId,
      };
      updateDonorMutation.mutate(donor, {
        onError: createErrorHandler(),
        onSuccess() {
          form.reset(values);
        },
      });
      return;
    }

    updateUserNotificationsMutation.mutate(
      {
        ...values,
        id: userId,
      } as unknown as DonorNotificationsPayload,
      {
        onSuccess() {
          form.reset(values);
        },
      },
    );
  };

  return (
    <div className="growfund-h-[var(--growfund-topbar-height)] growfund-bg-background-surface growfund-sticky growfund-top-0 growfund-z-header growfund-border-b growfund-border-b-border growfund-flex growfund-items-center">
      <Container className="growfund-flex growfund-items-center growfund-justify-between" size="sm">
        <PageTitle title={__('Settings', 'growfund')} />

        <div className="growfund-flex growfund-items-center growfund-gap-3">
          <Button
            variant="secondary"
            size="sm"
            disabled={
              !isCurrentFormDirty ||
              updateBackerMutation.isPending ||
              updateUserNotificationsMutation.isPending ||
              updateDonorMutation.isPending
            }
            onClick={() => {
              if (!form) return;
              form.reset();
            }}
          >
            {__('Discard', 'growfund')}
          </Button>
          <Button
            size="sm"
            disabled={
              !isCurrentFormDirty ||
              updateBackerMutation.isPending ||
              updateUserNotificationsMutation.isPending ||
              updateDonorMutation.isPending
            }
            loading={
              updateBackerMutation.isPending ||
              updateUserNotificationsMutation.isPending ||
              updateDonorMutation.isPending
            }
            onClick={() => {
              if (!form || !key || !userId) {
                return;
              }

              void form.handleSubmit(
                (values) => {
                  if (isBacker) {
                    handleBackerFormSubmit(values, form);
                  }

                  if (isDonor) {
                    handleDonorFormSubmit(values, form);
                  }

                  return;
                },
                (errors) => {
                  console.error(errors);
                },
              )();
            }}
          >
            {__('Save', 'growfund')}
          </Button>
        </div>
      </Container>
    </div>
  );
};

export default UserSettingsTopbar;
