import { z } from 'zod';

export const permissionSchema = z.object({
    id: z.number(),
    name: z.string(),
    group: z.string().nullable().optional(),
    guard_name: z.string().optional(),
});

export type Permission = z.infer<typeof permissionSchema>;

export const createPermissionSchema = z.object({
    name: z.string().min(1, 'Permission name is required').max(255, 'Permission name is too long'),
    group: z.string().optional(),
});

export type CreatePermissionFormData = z.infer<typeof createPermissionSchema>;

export const updatePermissionSchema = createPermissionSchema;
export type UpdatePermissionFormData = z.infer<typeof updatePermissionSchema>;
