import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { BackerFormSchema, BackerResponseSchema } from '@/features/backers/schemas/backer';
import { PledgeFormSchema } from '@/features/pledges/schemas/pledge-form';
import { getPaginatedResponseSchema } from '@/openapi/utils';

export function registerBackersPaths(registry: OpenAPIRegistry) {
  // GET /backers
  registry.registerPath({
    tags: ['Backers'],
    method: 'get',
    path: '/backers',
    summary: 'Get paginated list of backers',
    request: {
      query: z.object({
        search: z.string().nullish(),
        date_from: z.coerce.date().nullish(),
        date_to: z.coerce.date().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
        campaign_id: z.string().nullish(),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of backers',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(BackerResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /backers
  registry.registerPath({
    tags: ['Backers'],
    method: 'post',
    path: '/backers',
    summary: 'Create a new backer',
    request: {
      body: {
        content: {
          'application/json': {
            schema: BackerFormSchema._def.schema.merge(
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
        description: 'Backer created',
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

  // PUT /backers
  registry.registerPath({
    tags: ['Backers'],
    method: 'put',
    path: '/backers/{id}',
    summary: 'Update a backer by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: BackerFormSchema._def.schema.merge(
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
        description: 'Backer updated',
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

  // DELETE /backers
  registry.registerPath({
    tags: ['Backers'],
    method: 'delete',
    path: '/backers/{id}',
    summary: 'Delete a backer by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Backer deleted',
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
        description: 'Backer not found',
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

  // GET /backers/{id}/overview
  registry.registerPath({
    tags: ['Backers'],
    method: 'get',
    path: '/backers/{id}/overview',
    summary: 'Get a backer overview by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Backer overview retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: BackerResponseSchema,
          },
        },
      },
    },
  });

  // GET /backers/{backer_id}/pledges
  registry.registerPath({
    tags: ['Backers'],
    method: 'get',
    path: '/backers/{backer_id}/pledges',
    summary: 'Get a backer pledges by ID',
    request: {
      params: z.object({
        backer_id: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'Backer pledges retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: z.array(PledgeFormSchema),
          },
        },
      },
      404: {
        description: 'Backer not found',
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
