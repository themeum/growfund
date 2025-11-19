import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { TagPayloadSchema, TagResponseSchema } from '@/features/tags/schemas/tag';

export function registerTagsPaths(registry: OpenAPIRegistry) {
  // GET /tags
  registry.registerPath({
    tags: ['Tags'],
    method: 'get',
    path: '/tags',
    summary: 'Get all tags',
    responses: {
      200: {
        description: 'List of all tags',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(TagResponseSchema),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // POST /tags
  registry.registerPath({
    tags: ['Tags'],
    method: 'post',
    path: '/tags',
    summary: 'Create a new tag',
    request: {
      body: {
        content: {
          'application/json': {
            schema: TagPayloadSchema,
          },
        },
      },
    },
    responses: {
      201: {
        description: 'Tag created',
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

  // PUT /tags
  registry.registerPath({
    tags: ['Tags'],
    method: 'put',
    path: '/tags/{id}',
    summary: 'Update a tag by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: TagPayloadSchema,
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

  // DELETE /tags
  registry.registerPath({
    tags: ['Tags'],
    method: 'delete',
    path: '/tags/{id}',
    summary: 'Delete a tag by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Tag deleted',
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
        description: 'Tag not found',
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

  // PATCH /tags/bulk-actions
  registry.registerPath({
    tags: ['Tags'],
    method: 'patch',
    path: '/tags/bulk-actions',
    summary: 'Bulk actions for tags',
    request: {
      body: {
        content: {
          'application/json': {
            schema: z.object({
              action: z.enum(['delete']),
              ids: z.array(z.string()),
            }),
          },
        },
      },
    },
    responses: {
      207: {
        description: 'Bulk actions for tags',
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
}
