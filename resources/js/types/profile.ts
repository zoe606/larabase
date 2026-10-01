export type Gender = 'male' | 'female';

export interface Profile {
    id: number;
    user_id: number;
    name: string;
    phone: string | null;
    address: string | null;
    bio: string | null;
    date_of_birth: string | null;
    gender: Gender | null;
    timezone: string;
    locale: string;
    website: string | null;
    twitter: string | null;
    linkedin: string | null;
    github: string | null;
    avatar: string | null;
    avatar_medium: string | null;
    avatar_original: string | null;
    created_at: string;
    updated_at: string;
}

export interface ProfileFormData {
    name: string;
    email: string;
    phone: string;
    address: string;
    bio: string;
    date_of_birth: string;
    gender: Gender | '';
    timezone: string;
    locale: string;
    website: string;
    twitter: string;
    linkedin: string;
    github: string;
}
