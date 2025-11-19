import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { RewardResponseSchema } from '@/features/campaigns/schemas/reward';
import { RewardItemPayloadSchema } from '@/features/campaigns/schemas/reward-item';

export function registerRewardItemsPaths(registry: OpenAPIRegistry) {
  // GET /campaigns/{campaign_id}/reward-items
  registry.registerPath({
    tags: ['Reward Items'],
    method: 'get',
    path: '/campaigns/{campaign_id}/reward-items',
    summary: 'Get all reward items for a campaign',
    request: {
      params: z.object({
        campaign_id: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'List of all reward items for a campaign',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(RewardResponseSchema),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // POST /campaigns/{campaign_id}/reward-items
  registry.registerPath({
    tags: ['Reward Items'],
    method: 'post',
    path: '/campaigns/{campaign_id}/reward-items',
    summary: 'Create a new reward item for a campaign',
    request: {
      params: z.object({
        campaign_id: z.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: RewardItemPayloadSchema.merge(
              z.object({
                image: z.string(),
              }),
            ),
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Reward item created',
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

  // PUT /campaigns/{campaign_id}/reward-items/{id}
  registry.registerPath({
    tags: ['Reward Items'],
    method: 'put',
    path: '/campaigns/{campaign_id}/reward-items/{id}',
    summary: 'Update a reward item by ID',
    request: {
      params: z.object({
        campaign_id: z.string(),
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: RewardItemPayloadSchema.merge(
              z.object({
                image: z.string(),
              }),
            ),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Reward item updated',
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

  // DELETE /campaigns/{campaign_id}/reward-items/{id}
  registry.registerPath({
    tags: ['Reward Items'],
    method: 'delete',
    path: '/campaigns/{campaign_id}/reward-items/{id}',
    summary: 'Delete a reward item by ID',
    request: {
      params: z.object({
        campaign_id: z.string(),
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Reward item deleted',
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
        description: 'Reward item not found',
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
