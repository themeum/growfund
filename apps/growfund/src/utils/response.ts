import { toast } from 'sonner';
import { z } from 'zod';

import { type PaginatedResponse } from '@/types';

const parseResponse = <T>(response: T[], schema: z.ZodSchema) => {
  const result = z.array(schema).safeParse(response);
  if (!result.success) {
    console.error(result.error.errors);
    toast.error('Invalid Data Structure', {
      description:
        'There is a mismatch in the data structure. Please try again later or contact support.',
    });
    throw new Error(result.error.errors.map((error) => error.message).join(', '));
  }
  return response;
};

const parsePaginatedResponse = <T>(
  response: PaginatedResponse<T>,
  schema: z.ZodSchema,
): PaginatedResponse<T> | never => {
  if (__ENV_MODE__ !== 'development') {
    return response;
  }
  const result = z.array(schema).safeParse(response.results);

  if (!result.success) {
    console.error(result.error.errors);
    toast.error('Invalid Data Structure', {
      description:
        'There is a mismatch in the data structure. Please try again later or contact support.',
    });
    throw new Error(result.error.errors.map((error) => error.message).join(', '));
  }

  return response;
};

export { parsePaginatedResponse, parseResponse };
