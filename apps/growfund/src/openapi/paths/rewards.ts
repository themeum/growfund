import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { RewardResponseSchema, RewardSchema } from '@/features/campaigns/schemas/reward';

export function registerRewardsPaths(registry: OpenAPIRegistry) {
  // GET /campaigns/{campaign_id}/rewards
  registry.registerPath({
    tags: ['Rewards'],
    method: 'get',
    path: '/campaigns/{campaign_id}/rewards',
    summary: 'Get all rewards for a campaign',
    request: {
      params: z.object({
        campaign_id: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'List of all rewards for a campaign',
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

  // POST /campaigns/{campaign_id}/rewards
  registry.registerPath({
    tags: ['Rewards'],
    method: 'post',
    path: '/campaigns/{campaign_id}/rewards',
    summary: 'Create a new reward',
    request: {
      params: z.object({
        campaign_id: z.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: RewardSchema.merge(
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
        description: 'Reward created',
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

  // PUT /campaigns/{campaign_id}/rewards/{reward_id}
  registry.registerPath({
    tags: ['Rewards'],
    method: 'put',
    path: '/campaigns/{campaign_id}/rewards/{reward_id}',
    summary: 'Update a reward by ID',
    request: {
      params: z.object({
        campaign_id: z.string(),
        reward_id: z.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: RewardSchema.merge(
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
        description: 'Reward updated',
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

  // DELETE /campaigns/{campaign_id}/rewards/{reward_id}
  registry.registerPath({
    tags: ['Rewards'],
    method: 'delete',
    path: '/campaigns/{campaign_id}/rewards/{reward_id}',
    summary: 'Delete a reward by ID',
    request: {
      params: z.object({
        campaign_id: z.string(),
        reward_id: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'Reward deleted',
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
        description: 'Reward not found',
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
