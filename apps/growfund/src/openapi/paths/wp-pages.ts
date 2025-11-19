import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

export function registerWPPagesPaths(registry: OpenAPIRegistry) {
  // GET /wp-pages
  registry.registerPath({
    tags: ['WP Pages'],
    method: 'get',
    path: '/wp-pages',
    summary: 'List of all wp pages.',
    responses: {
      200: {
        description: 'Obtains the option value by key.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.array(
                z.object({
                  id: z.coerce.string(),
                  name: z.string(),
                  parent_id: z.string(),
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
