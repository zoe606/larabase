import { z } from 'zod';

export const roleSchema = z.object({
    id: z.number(),
    name: z.string(),
    guard_name: z.string().optional(),
    permissions: z
        .array(
            z.object({
                id: z.number(),
                name: z.string(),
            }),
        )
        .optional(),
});

export type Role = z.infer<typeof roleSchema>;

export const createRoleSchema = z.object({
    name: z.string().min(1, 'Role name is required').max(255, 'Role name is too long'),
    permissions: z.array(z.string()).optional(),
});

export type CreateRoleFormData = z.infer<typeof createRoleSchema>;

export const updateRoleSchema = createRoleSchema;
export type UpdateRoleFormData = z.infer<typeof updateRoleSchema>;
