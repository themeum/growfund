import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

export function registerOptionsPaths(registry: OpenAPIRegistry) {
  // GET /options
  registry.registerPath({
    tags: ['Options'],
    method: 'get',
    path: '/options',
    summary: 'Get specific option by key.',
    request: {
      query: z.object({
        key: z.string(),
      }),
    },
    responses: {
      200: {
        description: 'Obtains the option value by key.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.any().nullish(),
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // POST /options
  registry.registerPath({
    tags: ['Options'],
    method: 'post',
    path: '/options',
    summary: 'Update specific option by key.',
    request: {
      body: {
        content: {
          'application/json': {
            schema: z.object({
              key: z.string(),
              value: z.any(),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Option updated successfully.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.any().nullish(),
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
