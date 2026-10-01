import { z } from 'zod';

export const userSchema = z.object({
    id: z.number(),
    name: z.string(),
    email: z.string().email(),
    avatar: z.string().optional(),
    email_verified_at: z.string().nullable(),
    created_at: z.string(),
    updated_at: z.string(),
});

export type User = z.infer<typeof userSchema>;

export const createUserSchema = z
    .object({
        name: z.string().min(1, 'Name is required').max(255, 'Name is too long'),
        email: z.string().min(1, 'Email is required').email('Invalid email address'),
        password: z.string().min(8, 'Password must be at least 8 characters'),
        password_confirmation: z.string().min(1, 'Please confirm your password'),
        roles: z.array(z.string()).min(1, 'Select at least one role'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Passwords do not match',
        path: ['password_confirmation'],
    });

export type CreateUserFormData = z.infer<typeof createUserSchema>;

export const updateUserSchema = z
    .object({
        name: z.string().min(1, 'Name is required').max(255, 'Name is too long'),
        email: z.string().min(1, 'Email is required').email('Invalid email address'),
        password: z.string().min(8, 'Password must be at least 8 characters').optional().or(z.literal('')),
        password_confirmation: z.string().optional().or(z.literal('')),
        roles: z.array(z.string()).min(1, 'Select at least one role'),
    })
    .refine(
        (data) => {
            // Only validate confirmation if password is provided
            if (data.password && data.password.length > 0) {
                return data.password === data.password_confirmation;
            }
            return true;
        },
        {
            message: 'Passwords do not match',
            path: ['password_confirmation'],
        },
    );

export type UpdateUserFormData = z.infer<typeof updateUserSchema>;

export const profileSchema = z.object({
    name: z.string().min(1, 'Name is required').max(255, 'Name is too long'),
    email: z.string().min(1, 'Email is required').email('Invalid email address'),
});

export type ProfileFormData = z.infer<typeof profileSchema>;

export const passwordSchema = z
    .object({
        current_password: z.string().min(1, 'Current password is required'),
        password: z.string().min(8, 'Password must be at least 8 characters'),
        password_confirmation: z.string().min(1, 'Please confirm your password'),
    })
    .refine((data) => data.password === data.password_confirmation, {
        message: 'Passwords do not match',
        path: ['password_confirmation'],
    });

export type PasswordFormData = z.infer<typeof passwordSchema>;
