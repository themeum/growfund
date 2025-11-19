import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { getPaginatedResponseSchema } from '@/openapi/utils';

import {
  FundraiserFormSchema,
  FundraiserResponseSchema,
} from '@growfund/pro/features/fundraisers/schemas/fundraiser';


export function registerFundraisersPaths(registry: OpenAPIRegistry) {
  // GET /funraisers
  registry.registerPath({
    tags: ['Fundraisers'],
    method: 'get',
    path: '/fundraisers',
    summary: 'Get paginated list of fundraisers',
    request: {
      query: z.object({
        search: z.string().nullish(),
        status: z.string().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of fundraisers',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(FundraiserResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /funraisers
  registry.registerPath({
    tags: ['Fundraisers'],
    method: 'post',
    path: '/fundraisers',
    summary: 'Create a new backer',
    request: {
      body: {
        content: {
          'application/json': {
            schema: FundraiserFormSchema.merge(
              z.object({
                image: z.string().nullish(),
              }),
            ),
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Fundraiser created',
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

  // PUT /funraisers
  registry.registerPath({
    tags: ['Fundraisers'],
    method: 'put',
    path: '/fundraisers/{id}',
    summary: 'Update a fundraiser by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: FundraiserFormSchema.merge(
              z.object({
                image: z.string().nullish(),
              }),
            ),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Fundraiser updated',
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

  // PATCH /funraisers/{id}
  registry.registerPath({
    tags: ['Fundraisers'],
    method: 'delete',
    path: '/fundraisers/{id}',
    summary: 'Update the status of fundraiser by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: z.object({
              action: z.enum(['approve', 'decline']),
              status: z.string(),
              reason: z.string().nullish().describe('Required if action is "decline"'),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Fundraiser updated',
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
      404: {
        description: 'Fundraiser not found',
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
}
