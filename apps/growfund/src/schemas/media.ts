import { z } from 'zod';

import { type Prettify } from '@/types';

const MediaSchema = z.object({
  id: z.coerce.string(),
  filename: z.string().nullish(),
  url: z.string(),
  sizes: z
    .record(
      z.string(),
      z.object({
        height: z.number().nullish(),
        width: z.number().nullish(),
        url: z.string().nullish(),
        orientation: z.string().nullish(),
      }),
    )
    .or(z.array(z.string().nullish()))
    .nullish(),
  height: z.number().nullish(),
  width: z.number().nullish(),
  filesize: z.number().nullish(),
  mime: z.string().nullish(),
  type: z.string().nullish(),
  thumb: z
    .object({
      src: z.string(),
      width: z.number(),
      height: z.number(),
    })
    .nullish(),
  author: z.string().nullish(),
  authorName: z.string().nullish(),
  date: z.string().nullish(),
});
const GallerySchema = z.array(MediaSchema);
type GalleryFieldType = z.infer<typeof GallerySchema>;

const VideoSchema = MediaSchema.extend({
  poster: MediaSchema.nullish(),
});

type VideoField = Prettify<z.infer<typeof VideoSchema>>;
type MediaAttachment = Prettify<z.infer<typeof MediaSchema>>;
type VideoAttachment = Prettify<z.infer<typeof VideoSchema>>;

export {
  GallerySchema,
  MediaSchema,
  VideoSchema,
  type GalleryFieldType,
  type MediaAttachment,
  type VideoAttachment,
  type VideoField,
};
