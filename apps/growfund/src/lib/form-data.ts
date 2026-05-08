import { format } from 'date-fns';

import { DATE_FORMATS } from '@/lib/date';
import { isDefined } from '@/utils';

type FormDataValue =
  | string
  | number
  | boolean
  | Date
  | File
  | Blob
  | null
  | undefined
  | FormDataObject;

interface FormDataObject {
  [key: string]: FormDataValue;
}

const isPlainObject = (value: unknown): value is Record<string, unknown> => {
  return (
    typeof value === 'object' &&
    value !== null &&
    !(value instanceof File) &&
    !(value instanceof Blob) &&
    !(value instanceof Date) &&
    !Array.isArray(value)
  );
};

export const prepareFormData = (
  formData: FormData,
  data: Record<string, unknown>,
  parentKey = '',
): void => {
  for (const [key, value] of Object.entries(data)) {
    const fullKey = parentKey ? `${parentKey}[${key}]` : key;

    if (!isDefined(value)) {
      formData.append(fullKey, '');
    } else if (value instanceof File || value instanceof Blob) {
      formData.append(fullKey, value);
    } else if (value instanceof Date) {
      formData.append(fullKey, format(value, DATE_FORMATS.ATOM));
    } else if (Array.isArray(value)) {
      for (const [index, item] of value.entries()) {
        prepareFormData(formData, { [index]: item as unknown }, fullKey);
      }
    } else if (isPlainObject(value)) {
      prepareFormData(formData, value, fullKey);
    } else if (
      typeof value === 'string' ||
      typeof value === 'number' ||
      typeof value === 'boolean'
    ) {
      formData.append(fullKey, String(value));
    }
  }
};
