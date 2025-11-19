/* eslint-disable @typescript-eslint/unified-signatures */
import { type UseQueryResult } from '@tanstack/react-query';
import type React from 'react';

import { isDefined } from '@/utils';

export function matchQueryStatus<T>(
  query: UseQueryResult<T>,
  options: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    Empty: React.ReactNode;
    Success: (
      data: UseQueryResult<T> & { data: NonNullable<UseQueryResult<T>['data']> },
    ) => React.ReactNode;
  },
): React.ReactNode;

export function matchQueryStatus<T>(
  query: UseQueryResult<T>,
  options: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    Success: (data: UseQueryResult<T>) => React.ReactNode;
  },
): React.ReactNode;

export function matchQueryStatus<T>(
  query: UseQueryResult<T>,
  {
    Loading,
    Error,
    Empty,
    Success,
  }: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    Empty?: React.ReactNode;
    Success: (data: UseQueryResult<T>) => React.ReactNode;
  },
): React.ReactNode {
  if (query.isLoading) {
    return Loading;
  }

  if (query.isError) {
    return typeof Error === 'function' ? Error(query.error) : Error;
  }

  const isEmpty = !isDefined(query.data) || (Array.isArray(query.data) && query.data.length === 0);

  if (isEmpty && Empty) {
    return Empty;
  }

  return Success(query);
}
