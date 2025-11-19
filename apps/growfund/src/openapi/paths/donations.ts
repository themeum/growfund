import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { DonationFormSchema, DonationResponseSchema } from '@/features/campaigns/schemas/campaign';
import { getPaginatedResponseSchema } from '@/openapi/utils';

export function registerDonationsPaths(registry: OpenAPIRegistry) {
  // GET /donations
  registry.registerPath({
    tags: ['Donations'],
    method: 'get',
    path: '/donations',
    summary: 'Get paginated list of donations',
    request: {
      query: z.object({
        search: z.string().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
        campaign_id: z.string().nullish(),
        start_date: z.string().nullish(),
        end_date: z.string().nullish(),
        status: z.string().nullish(),
        fund_id: z.string().nullish(),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of donations',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(DonationResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /donations
  registry.registerPath({
    tags: ['Donations'],
    method: 'post',
    path: '/donations',
    summary: 'Create a new donation (TODO: Add proper schema)',
    request: {
      body: {
        content: {
          'application/json': {
            schema: DonationFormSchema,
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Donation created',
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

  // PUT /donations/{id}
  registry.registerPath({
    tags: ['Donations'],
    method: 'put',
    path: '/donations/{id}',
    summary: 'Update a donation by ID (TODO: Add proper schema)',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: DonationFormSchema,
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Donation updated',
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

  // GET /donations/{id}
  registry.registerPath({
    tags: ['Donations'],
    method: 'get',
    path: '/donations/{id}',
    summary: 'Get a donation by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Donation retrieved',
        content: {
          'application/json': {
            schema: DonationResponseSchema,
          },
        },
      },
      404: {
        description: 'Donation not found',
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
