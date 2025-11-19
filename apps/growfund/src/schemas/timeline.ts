import { __ } from '@wordpress/i18n';
import z from 'zod';

const CommentSchema = z.object({
  comment: z
    .string()
    .min(1, { message: __('Comment message is required', 'growfund') })
    .default(''),
});

type CommentSchemaType = z.infer<typeof CommentSchema>;

export { CommentSchema, type CommentSchemaType };
