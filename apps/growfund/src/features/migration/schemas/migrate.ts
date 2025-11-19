import z from 'zod';

const MigrationProcessEnum = z.enum(['migrate-campaigns', 'migrate-contributions', 'final']);

type MigrationProcess = z.infer<typeof MigrationProcessEnum>;

const MigrateSchema = z.object({
  step: MigrationProcessEnum,
  is_running: z.boolean(),
  total: z.number(),
  completed: z.number(),
});

type Migrate = z.infer<typeof MigrateSchema>;

export { MigrateSchema, type Migrate, MigrationProcessEnum, type MigrationProcess };
