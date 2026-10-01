import { z } from 'zod';

// Define the base menu schema without recursive children first
const baseMenuSchema = z.object({
    id: z.number(),
    title: z.string(),
    route: z.string().nullable(),
    icon: z.string(),
    parent_id: z.number().nullable(),
    order: z.number(),
    permission_name: z.string().nullable(),
});

// Define the menu type with recursive children
export type Menu = z.infer<typeof baseMenuSchema> & {
    children?: Menu[];
};

// Create the full schema with lazy children
export const menuSchema: z.ZodType<Menu> = baseMenuSchema.extend({
    children: z.lazy(() => z.array(menuSchema)).optional(),
});

export const createMenuSchema = z.object({
    title: z.string().min(1, 'Menu title is required').max(255, 'Menu title is too long'),
    route: z.string().nullable().optional(),
    icon: z.string().min(1, 'Icon is required'),
    parent_id: z.number().nullable().optional(),
    order: z.number().optional(),
    permission_name: z.string().nullable().optional(),
});

export type CreateMenuFormData = z.infer<typeof createMenuSchema>;

export const updateMenuSchema = createMenuSchema;
export type UpdateMenuFormData = z.infer<typeof updateMenuSchema>;
