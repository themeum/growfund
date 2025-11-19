import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { getPaginatedResponseSchema } from '@/openapi/utils';

import { FundPayloadSchema, FundResponseSchema } from '@growfund/pro/features/funds/schemas/fund';

export function registerFundsPaths(registry: OpenAPIRegistry) {
  // GET /funds
  registry.registerPath({
    tags: ['Funds'],
    method: 'get',
    path: '/funds',
    summary: 'Get paginated list of funds',
    request: {
      query: z.object({
        search: z.string().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of funds',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(FundResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /funds
  registry.registerPath({
    tags: ['Funds'],
    method: 'post',
    path: '/funds',
    summary: 'Create a new fund',
    request: {
      body: {
        content: {
          'application/json': {
            schema: FundPayloadSchema,
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Fund created',
        content: {
          'application/json': {
            schema: z.object({
              data: z.object({
                id: z.coerce.string(),
              }),
              message: z.string().nullish(),
            }),
          },
        },
      },
      422: {
        description: 'Validation error',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              message: z.string(),
              errors: z.array(
                z.object({
                  field: z.string(),
                  message: z.string(),
                }),
              ),
            }),
          },
        },
      },
    },
  });

  // PUT /funds/{id}
  registry.registerPath({
    tags: ['Funds'],
    method: 'put',
    path: '/funds/{id}',
    summary: 'Update a fund by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: FundPayloadSchema,
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Fund updated',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.boolean().default(true),
              message: z.string().nullish(),
            }),
          },
        },
      },
      422: {
        description: 'Validation error',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              message: z.string(),
              errors: z.array(
                z.object({
                  field: z.string(),
                  message: z.string(),
                }),
              ),
            }),
          },
        },
      },
    },
  });

  // DELETE /funds
  registry.registerPath({
    tags: ['Funds'],
    method: 'delete',
    path: '/funds/{id}',
    summary: 'Delete a fund by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Fund deleted',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.boolean().default(true),
              message: z.string().nullish(),
            }),
          },
        },
      },
      404: {
        description: 'Fund not found',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              message: z.string(),
            }),
          },
        },
      },
    },
  });

  // GET /funds/{id}
  registry.registerPath({
    tags: ['Funds'],
    method: 'get',
    path: '/funds/{id}',
    summary: 'Get a fund by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Fund retrieved',
        content: {
          'application/json': {
            schema: FundResponseSchema,
          },
        },
      },
      404: {
        description: 'Fund not found',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              message: z.string(),
            }),
          },
        },
      },
    },
  });

  // GET /funds/{id}/details
  registry.registerPath({
    tags: ['Funds'],
    method: 'get',
    path: '/funds/{id}/details',
    summary: 'Get a fund details by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Fund details retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: FundResponseSchema,
          },
        },
      },
    },
  });
}
