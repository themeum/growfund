import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { AppConfigSchema } from '@/features/settings/schemas/settings';

export function registerAppConfigPaths(registry: OpenAPIRegistry) {
  // GET /app-config
  registry.registerPath({
    tags: ['App Config'],
    method: 'get',
    path: '/app-config',
    summary: 'Get the current application configurations.',
    responses: {
      200: {
        description: 'Obtains the current application configurations.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: AppConfigSchema,
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });

  // POST /app-config
  registry.registerPath({
    tags: ['App Config'],
    method: 'post',
    path: '/app-config',
    summary: 'Update the current application configurations.',
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
        description: 'Application configurations updated successfully.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(z.object({}).nullable()),
              message: z.string().nullish(),
            }),
          },
        },
      },
      400: {
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
