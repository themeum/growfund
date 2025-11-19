import type { UseQueryResult } from '@tanstack/react-query';

/**
 * QueryFn — supports both parameterized and non-parameterized queries.
 */
export type QueryFn<TParams = void, TData = unknown, TError = unknown> = [TParams] extends [
  boolean | undefined,
]
  ? (enabled?: boolean) => UseQueryResult<TData, TError>
  : (params: TParams, enabled?: boolean) => UseQueryResult<TData, TError>;

/**
 * Entry type for a registered query
 */
export interface QueryRegistryEntry<TParams = void, TData = unknown, TError = unknown> {
  query: QueryFn<TParams, TData, TError>;
  description?: string;
  category?: string;
}

/**
 * Core registry class
 */
export class QueryRegistry {
  // Use `unknown` instead of `any` (ESLint clean)
  private readonly queries = new Map<string, QueryRegistryEntry>();

  register<TParams = void, TData = unknown, TError = unknown>(
    name: string,
    query: QueryFn<TParams, TData, TError>,
    options?: Omit<QueryRegistryEntry<TParams, TData, TError>, 'query'>,
  ): void {
    // Store as `unknown` to allow mixed function signatures
    const entry: QueryRegistryEntry = {
      query: query as QueryFn,
      ...options,
    };
    this.queries.set(name, entry);
  }

  get<TParams = void, TData = unknown, TError = unknown>(
    name: string,
  ): QueryFn<TParams, TData, TError> | null {
    const entry = this.queries.get(name);
    return entry ? (entry.query as QueryFn<TParams, TData, TError>) : null;
  }

  exists(name: string): boolean {
    return this.queries.has(name);
  }

  unregister(name: string): boolean {
    return this.queries.delete(name);
  }

  clear(): void {
    this.queries.clear();
  }

  getNames(): string[] {
    return Array.from(this.queries.keys());
  }
}

/**
 * Global singleton registry
 */
declare global {
  interface Window {
    __GROWFUND_QUERY_REGISTRY__?: QueryRegistry;
  }
}

export function createQueryRegistry(): QueryRegistry {
  if (!window.__GROWFUND_QUERY_REGISTRY__) {
    window.__GROWFUND_QUERY_REGISTRY__ = new QueryRegistry();
  }
  return window.__GROWFUND_QUERY_REGISTRY__;
}

const queryRegistry = createQueryRegistry();

export const registerQuery = <TParams = void, TData = unknown, TError = unknown>(
  name: string,
  query: QueryFn<TParams, TData, TError>,
  options?: Omit<QueryRegistryEntry<TParams, TData, TError>, 'query'>,
): void => {
  queryRegistry.register(name, query, options);
};

export const getQuery = <TParams = void, TData = unknown, TError = unknown>(
  name: string,
): QueryFn<TParams, TData, TError> | null => {
  return queryRegistry.get<TParams, TData, TError>(name);
};

export const useQueryHook = getQuery;

export const queryRegistryInstance = queryRegistry;
