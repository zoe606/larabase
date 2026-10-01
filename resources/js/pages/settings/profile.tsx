import { type BreadcrumbItem } from '@/types';
import type { Profile as ProfileType } from '@/types/profile';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

import { AvatarUpload, DatePicker, DeleteUser, HeadingSmall, InputError } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
    profile: ProfileType | null;
}

const timezones = [
    { value: 'Asia/Jakarta', label: 'Asia/Jakarta (WIB)' },
    { value: 'Asia/Makassar', label: 'Asia/Makassar (WITA)' },
    { value: 'Asia/Jayapura', label: 'Asia/Jayapura (WIT)' },
    { value: 'Asia/Singapore', label: 'Asia/Singapore' },
    { value: 'Asia/Tokyo', label: 'Asia/Tokyo' },
    { value: 'America/New_York', label: 'America/New_York' },
    { value: 'America/Los_Angeles', label: 'America/Los_Angeles' },
    { value: 'Europe/London', label: 'Europe/London' },
    { value: 'UTC', label: 'UTC' },
];

const locales = [
    { value: 'id', label: 'Bahasa Indonesia' },
    { value: 'en', label: 'English' },
];

export default function Profile({ mustVerifyEmail, status, profile }: Props) {
    const { auth } = usePage().props;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: profile?.name || auth.user.name,
        email: auth.user.email,
        phone: profile?.phone || '',
        address: profile?.address || '',
        bio: profile?.bio || '',
        date_of_birth: profile?.date_of_birth || '',
        gender: profile?.gender || '',
        timezone: profile?.timezone || 'Asia/Jakarta',
        locale: profile?.locale || 'id',
        website: profile?.website || '',
        twitter: profile?.twitter || '',
        linkedin: profile?.linkedin || '',
        github: profile?.github || '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.update'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-8">
                    {/* Avatar Section */}
                    <div className="space-y-4">
                        <HeadingSmall title="Profile Photo" description="Upload a profile picture" />
                        <AvatarUpload
                            name={data.name}
                            avatarUrl={profile?.avatar}
                            uploadRoute={route('profile.avatar.update')}
                            deleteRoute={route('profile.avatar.delete')}
                        />
                    </div>

                    <hr className="border-border" />

                    {/* Basic Information */}
                    <div className="space-y-6">
                        <HeadingSmall title="Basic Information" description="Update your name and email address" />

                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                        autoComplete="name"
                                        placeholder="Full name"
                                    />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email address</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        required
                                        autoComplete="username"
                                        placeholder="Email address"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                            </div>

                            {mustVerifyEmail && auth.user.email_verified_at === null && (
                                <div>
                                    <p className="mt-2 text-sm text-gray-800 dark:text-gray-200">
                                        Your email address is unverified.
                                        <Link
                                            href={route('verification.send')}
                                            method="post"
                                            as="button"
                                            className="ml-1 rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:ring-2 focus:ring-offset-2 focus:outline-hidden dark:text-gray-400 dark:hover:text-gray-100"
                                        >
                                            Click here to re-send the verification email.
                                        </Link>
                                    </p>

                                    {status === 'verification-link-sent' && (
                                        <div className="mt-2 text-sm font-medium text-green-600">
                                            A new verification link has been sent to your email address.
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Phone</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        value={data.phone}
                                        onChange={(e) => setData('phone', e.target.value)}
                                        placeholder="+62 812 3456 7890"
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="date_of_birth">Date of Birth</Label>
                                    <DatePicker
                                        id="date_of_birth"
                                        value={data.date_of_birth}
                                        onChange={(date) => setData('date_of_birth', date || '')}
                                    />
                                    <InputError message={errors.date_of_birth} />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <Select value={data.gender} onValueChange={(value) => setData('gender', value)}>
                                        <SelectTrigger id="gender">
                                            <SelectValue placeholder="Select gender" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="male">Male</SelectItem>
                                            <SelectItem value="female">Female</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.gender} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="timezone">Timezone</Label>
                                    <Select value={data.timezone} onValueChange={(value) => setData('timezone', value)}>
                                        <SelectTrigger id="timezone">
                                            <SelectValue placeholder="Select timezone" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {timezones.map((tz) => (
                                                <SelectItem key={tz.value} value={tz.value}>
                                                    {tz.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.timezone} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="locale">Language</Label>
                                <Select value={data.locale} onValueChange={(value) => setData('locale', value)}>
                                    <SelectTrigger id="locale" className="sm:w-1/2">
                                        <SelectValue placeholder="Select language" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {locales.map((loc) => (
                                            <SelectItem key={loc.value} value={loc.value}>
                                                {loc.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                <InputError message={errors.locale} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="address">Address</Label>
                                <Textarea
                                    id="address"
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="Your address"
                                    rows={2}
                                />
                                <InputError message={errors.address} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="bio">Bio</Label>
                                <Textarea
                                    id="bio"
                                    value={data.bio}
                                    onChange={(e) => setData('bio', e.target.value)}
                                    placeholder="Tell us about yourself"
                                    rows={3}
                                />
                                <InputError message={errors.bio} />
                            </div>

                            <hr className="border-border" />

                            {/* Social Links */}
                            <div className="space-y-4">
                                <h3 className="text-sm font-medium">Social Links</h3>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="website">Website</Label>
                                        <Input
                                            id="website"
                                            type="url"
                                            value={data.website}
                                            onChange={(e) => setData('website', e.target.value)}
                                            placeholder="https://yourwebsite.com"
                                        />
                                        <InputError message={errors.website} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="twitter">Twitter / X</Label>
                                        <Input
                                            id="twitter"
                                            value={data.twitter}
                                            onChange={(e) => setData('twitter', e.target.value)}
                                            placeholder="username"
                                        />
                                        <InputError message={errors.twitter} />
                                    </div>
                                </div>

                                <div className="grid gap-4 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="linkedin">LinkedIn</Label>
                                        <Input
                                            id="linkedin"
                                            value={data.linkedin}
                                            onChange={(e) => setData('linkedin', e.target.value)}
                                            placeholder="username"
                                        />
                                        <InputError message={errors.linkedin} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="github">GitHub</Label>
                                        <Input
                                            id="github"
                                            value={data.github}
                                            onChange={(e) => setData('github', e.target.value)}
                                            placeholder="username"
                                        />
                                        <InputError message={errors.github} />
                                    </div>
                                </div>
                            </div>

                            <div className="flex items-center gap-4">
                                <Button disabled={processing}>Save Changes</Button>

                                <Transition
                                    show={recentlySuccessful}
                                    enter="transition ease-in-out"
                                    enterFrom="opacity-0"
                                    leave="transition ease-in-out"
                                    leaveTo="opacity-0"
                                >
                                    <p className="text-sm text-gray-600 dark:text-gray-400">Saved</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>

                <DeleteUser />
            </SettingsLayout>
        </AppLayout>
    );
}
