import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

export function registerValidateEmailPaths(registry: OpenAPIRegistry) {
  // POST /validate-email
  registry.registerPath({
    tags: ['Email Validation'],
    method: 'post',
    path: '/validate-email',
    summary: 'Validate the provided email.',
    request: {
      body: {
        content: {
          'application/json': {
            schema: z.object({
              email: z.string().email(),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Email is available .',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.boolean(),
              message: z.string().nullish(),
            }),
          },
        },
      },
      400: {
        description: 'Email is already in use.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(false),
              message: z.string().nullish(),
            }),
          },
        },
      },
      422: {
        description: 'Validation error.',
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
              message: z.string().nullish(),
            }),
          },
        },
      },
    },
  });
}
