import { __ } from '@wordpress/i18n';
import { useCallback } from 'react';
import { type FieldValues, type Path, type UseFormReturn } from 'react-hook-form';

import { type ErrorResponse } from '@/lib/api';
import { getObjectEntries, isDefined, isErrorResponse, isObject } from '@/utils';

const useFormErrorHandler = <T extends FieldValues>(form: UseFormReturn<T> | null) => {
  const setFormErrors = useCallback(
    (errors: Record<string, string>) => {
      if (!form) return;
      for (const [field, message] of getObjectEntries(errors)) {
        form.setError.call(null, field as Path<T>, {
          type: 'server',
          message,
        });
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [form?.setError],
  );

  const mapServerErrors = (errorResponse: ErrorResponse) => {
    if (!isDefined(errorResponse.errors) || !isObject(errorResponse.errors)) {
      return;
    }

    setFormErrors(errorResponse.errors);
  };

  const createErrorHandler = () => {
    return (error: ErrorResponse | Error) => {
      if (isErrorResponse(error)) {
        const status = error.response?.status;

        if (isDefined(status) && [400, 422].includes(status)) {
          mapServerErrors(error);
        }

        return error.message;
      }

      return __('An unknown error occurred', 'growfund');
    };
  };

  const clearServerErrors = useCallback(() => {
    if (!form) return;
    const fieldsWithServerErrors = Object.keys(form.formState.errors).filter(
      (field) => form.formState.errors[field]?.type === 'server',
    );

    for (const field of fieldsWithServerErrors) {
      form.clearErrors.call(null, field as Path<T>);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [form?.clearErrors, form?.formState.errors]);

  return {
    mapServerErrors,
    createErrorHandler,
    clearServerErrors,
  };
};

export { useFormErrorHandler };
