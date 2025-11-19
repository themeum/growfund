import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';


import {
  CampaignFormSchema,
  CampaignResponseSchema,
  CampaignStatusSchema,
} from '@/features/campaigns/schemas/campaign';
import { getPaginatedResponseSchema } from '@/openapi/utils';

import { PostUpdateSchema } from '@growfund/pro/schemas/post-update';

export function registerCampaignPaths(registry: OpenAPIRegistry) {
  // GET /campaigns
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'get',
    path: '/campaigns',
    summary: 'List all campaigns',
    request: {
      query: z.object({
        search: z.string().nullish(),
        status: z.string().nullish(),
        start_date: z.coerce.date().nullish(),
        end_date: z.coerce.date().nullish(),
        page: z.number().default(1),
        limit: z.number().default(10),
      }),
    },
    responses: {
      200: {
        description: 'Paginated list of campaigns',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: getPaginatedResponseSchema(CampaignResponseSchema),
            }),
          },
        },
      },
    },
  });

  // POST /campaigns
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'post',
    path: '/campaigns',
    summary: 'Create a new draft campaign',
    responses: {
      201: {
        description: 'Campaign created',
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

  // PUT /campaigns
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'put',
    path: '/campaigns/{id}',
    summary: 'Update a campaign by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: CampaignFormSchema.merge(
              z.object({
                images: z.array(z.string()).nullish(),
                video: z.string().nullish(),
                rewards: z.array(z.string()).nullish(),
              }),
            ),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Campaign updated',
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

  // DELETE /campaigns
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'delete',
    path: '/campaigns/{id}',
    summary: 'Delete a campaign by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Campaign deleted',
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
      500: {
        description: 'Server error',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              errors: z.array(
                z.object({
                  field: z.string(),
                  message: z.string(),
                }),
              ),
              message: z.string(),
            }),
          },
        },
      },
    },
  });

  // GET /campaigns/{id}
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'get',
    path: '/campaigns/{id}',
    summary: 'Get a campaign by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Campaign retrieved',
        content: {
          'application/json': {
            schema: CampaignResponseSchema,
          },
        },
      },
    },
  });

  // GET /campaigns/{id}/overview
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'get',
    path: '/campaigns/{id}/overview',
    summary: 'Get a campaign overview by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Campaign overview retrieved (TODO: Add proper schema)',
        content: {
          'application/json': {
            schema: CampaignResponseSchema,
          },
        },
      },
    },
  });

  // PATCH /campaigns/{id}
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'patch',
    path: '/campaigns/{id}',
    summary: 'Update a campaign status by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: z.object({
              status: z.enum([...CampaignStatusSchema.options, 'declined']),
              decline_reason: z
                .string()
                .describe('Decline reason is required if status is declined')
                .nullish(),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Campaign updated',
        content: {
          'application/json': {
            schema: z.object({
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

  //PATCH /campaigns/bulk-actions
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'patch',
    path: '/campaigns/bulk-actions',
    summary: 'Apply bulk actions to campaigns',
    request: {
      body: {
        content: {
          'application/json': {
            schema: z.object({
              ids: z.array(z.string()),
              action: z.enum(['delete', 'activate', 'deactivate']),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Applied bulk action on campaigns by ids',
        content: {
          'application/json': {
            schema: z.object({
              data: z.boolean().default(true),
              message: z.string(),
            }),
          },
        },
      },
    },
  });

  //POST /campaigns/{campaign_id}/post-update
  registry.registerPath({
    tags: ['Campaigns'],
    method: 'post',
    path: '/campaigns/{campaign_id}/post-update',
    summary: 'Create a new campaign post update',
    request: {
      params: z.object({
        campaign_id: z.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: PostUpdateSchema,
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Campaign post update created',
        content: {
          'application/json': {
            schema: z.object({
              data: z.object({
                id: z.coerce.string(),
              }),
              message: z.string(),
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
}
