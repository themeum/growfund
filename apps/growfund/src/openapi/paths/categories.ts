import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import {
  CategoryPayloadSchema,
  CategoryResponseSchema,
} from '@/features/categories/schemas/category';

export function registerCategoriesPaths(registry: OpenAPIRegistry) {
  // GET /categories
  registry.registerPath({
    tags: ['Categories'],
    method: 'get',
    path: '/categories',
    summary: 'Get all categories',
    responses: {
      200: {
        description: 'List of all categories',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(CategoryResponseSchema),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // POST /categories
  registry.registerPath({
    tags: ['Categories'],
    method: 'post',
    path: '/categories',
    summary: 'Create a new category',
    request: {
      body: {
        content: {
          'application/json': {
            schema: CategoryPayloadSchema.merge(
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
        description: 'Category created',
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

  // PUT /categories
  registry.registerPath({
    tags: ['Categories'],
    method: 'put',
    path: '/categories/{id}',
    summary: 'Update a category by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
      body: {
        content: {
          'application/json': {
            schema: CategoryPayloadSchema.merge(
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

  // DELETE /categories
  registry.registerPath({
    tags: ['Categories'],
    method: 'delete',
    path: '/categories/{id}',
    summary: 'Delete a category by ID',
    request: {
      params: z.object({
        id: z.coerce.string(),
      }),
    },
    responses: {
      200: {
        description: 'Category deleted',
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
        description: 'Category not found',
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

  // GET /categories/top-level
  registry.registerPath({
    tags: ['Categories'],
    method: 'get',
    path: '/categories/top-level',
    summary: 'Get all top level categories',
    responses: {
      200: {
        description: 'List of all top level categories',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(CategoryResponseSchema),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  //GET /categories/{parent_id}/subcategories'
  registry.registerPath({
    tags: ['Categories'],
    method: 'get',
    path: '/categories/{parent_id}/subcategories',
    summary: 'Get all sub categories',
    request: {
      params: z.object({
        parent_id: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'List of all sub categories',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(CategoryResponseSchema),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // PATCH /categories/bulk-actions
  registry.registerPath({
    tags: ['Categories'],
    method: 'patch',
    path: '/categories/bulk-actions',
    summary: 'Bulk actions for categories',
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
        description: 'Bulk actions for categories',
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
