import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { DonationResponseSchema } from '@/features/campaigns/schemas/campaign';
import {
  DonorResponseSchema,
  DonorOverviewSchema,
  DonorPayloadSchema,
} from '@/features/donors/schemas/donor';
import { getPaginatedResponseSchema } from '@/openapi/utils';

export function registerDonorsPaths(registry: OpenAPIRegistry) {
  // GET /donors
  registry.registerPath({
    tags: ['Donors'],
    method: 'get',
    path: '/donors',
    summary: 'Get paginated list of donors',
    request: {
      query: z.object({
        search: z.string().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
        orderBy: z.string().default('DESC'),
        order: z.string().default('ID'),
        start_date: z.string().nullish(),
        end_date: z.string().nullish(),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of donors',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(DonorResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /donors
  registry.registerPath({
    tags: ['Donors'],
    method: 'post',
    path: '/donors',
    summary: 'Create a new donor',
    request: {
      body: {
        content: {
          'application/json': {
            schema: DonorPayloadSchema.merge(
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
        description: 'Donor created',
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

  // PUT /donors/{id}
  registry.registerPath({
    tags: ['Donors'],
    method: 'put',
    path: '/donors/{id}',
    summary: 'Update a donor by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: DonorPayloadSchema.merge(
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
        description: 'Donor updated',
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

  // GET /donors/{id}/overview
  registry.registerPath({
    tags: ['Donors'],
    method: 'get',
    path: '/donors/{id}/overview',
    summary: 'Get a donor overview by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Donor overview retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: DonorOverviewSchema,
          },
        },
      },
    },
  });

  //GET /donors/{id}/donations
  registry.registerPath({
    tags: ['Donors'],
    method: 'get',
    path: '/donors/{id}/donations',
    summary: 'Get a donor donations by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Donor donations retrieved (TODO: Add proper schema)',
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
}
