import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { PledgeFormSchema } from '@/features/pledges/schemas/pledge-form';
import { getPaginatedResponseSchema } from '@/openapi/utils';

export function registerPledgesPaths(registry: OpenAPIRegistry) {
  // GET /pledges
  registry.registerPath({
    tags: ['Pledges'],
    method: 'get',
    path: '/pledges',
    summary: 'Get paginated list of pledges',
    request: {
      query: z.object({
        search: z.string().nullish(),
        status: z.string().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
        campaign_id: z.string().nullish(),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of pledges (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(PledgeFormSchema),
            }),
          },
        },
      },
    },
  });

  // POST /pledges
  registry.registerPath({
    tags: ['Pledges'],
    method: 'post',
    path: '/pledges',
    summary: 'Create a new pledge (TODO: Add proper schema)',
    request: {
      body: {
        content: {
          'application/json': {
            schema: PledgeFormSchema,
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Pledge created',
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

  // PUT /pledges
  registry.registerPath({
    tags: ['Pledges'],
    method: 'put',
    path: '/pledges/{id}',
    summary: 'Update a pledge by ID (TODO: Add proper schema)',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: PledgeFormSchema,
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Pledge updated',
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

  // GET /pledges/{id}
  registry.registerPath({
    tags: ['Pledges'],
    method: 'get',
    path: '/pledges/{id}',
    summary: 'Get a pledge by ID (TODO: Add proper schema)',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Pledge retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: PledgeFormSchema,
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
        description: 'Pledge not found',
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
