import { type OpenAPIRegistry } from '@asteasolutions/zod-to-openapi';
import { z } from 'zod';

import { GallerySchema } from '@/schemas/media';

export function registerMediaPaths(registry: OpenAPIRegistry) {
  // POST /upload-media
  registry.registerPath({
    tags: ['Media'],
    method: 'post',
    path: '/upload-media',
    summary: 'Upload media files.',
    request: {
      body: {
        content: {
          'application/json': {
            schema: z.object({
              media: z.array(z.instanceof(File)),
            }),
          },
        },
      },
    },
    responses: {
      200: {
        description: 'Media files are uploaded successfully.',
        content: {
          'application/json': {
            schema: z.object({
              success: z.boolean().default(true),
              data: z.object({
                media: GallerySchema,
              }),
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
