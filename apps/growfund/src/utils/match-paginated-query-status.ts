import { type UseQueryResult } from '@tanstack/react-query';
import type React from 'react';

import { type PaginatedResponse } from '@/types';
import { isDefined } from '@/utils';

export function matchPaginatedQueryStatus<T>(
  query: UseQueryResult<T>,
  options: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    Empty: React.ReactNode;
    EmptyResult?: React.ReactNode;
    Success: (
      data: UseQueryResult<T> & { data: NonNullable<UseQueryResult<T>['data']> },
      emptyResult?: React.ReactNode | null,
    ) => React.ReactNode;
  },
): React.ReactNode;

export function matchPaginatedQueryStatus<T>(
  query: UseQueryResult<PaginatedResponse<T>>,
  options: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    EmptyResult?: React.ReactNode;
    Success: (data: UseQueryResult<T>) => React.ReactNode;
  },
): React.ReactNode;

export function matchPaginatedQueryStatus<T>(
  query: UseQueryResult<T>,
  {
    Loading,
    Error,
    Empty,
    EmptyResult,
    Success,
  }: {
    Loading: React.ReactNode;
    Error: React.ReactNode | ((error: unknown) => React.ReactNode);
    Empty?: React.ReactNode;
    EmptyResult?: React.ReactNode;
    Success: (data: UseQueryResult<T>, emptyResult?: React.ReactNode | null) => React.ReactNode;
  },
): React.ReactNode {
  if (query.isLoading) {
    return Loading;
  }

  if (query.isError) {
    return typeof Error === 'function' ? Error(query.error) : Error;
  }

  const data = query.data as PaginatedResponse<T> | undefined;

  const isEmpty = !isDefined(data?.overall) || data.overall === 0;

  if (isEmpty && Empty) {
    return Empty;
  }

  const noResult = !isDefined(data?.results) || data.results.length === 0;

  const emptyResult = noResult && EmptyResult ? EmptyResult : null;
  return Success(query, emptyResult);
}
