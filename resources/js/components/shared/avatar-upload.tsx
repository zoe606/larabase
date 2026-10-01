import { DeleteConfirmation } from '@/components/shared/delete-confirmation';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/lib/initials';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { Camera, Loader2, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
import { toast } from 'sonner';

interface AvatarUploadProps {
    name: string;
    avatarUrl?: string | null;
    uploadRoute: string;
    deleteRoute: string;
    className?: string;
    size?: 'sm' | 'md' | 'lg';
}

const sizeClasses = {
    sm: 'h-16 w-16',
    md: 'h-24 w-24',
    lg: 'h-32 w-32',
};

export function AvatarUpload({ name, avatarUrl, uploadRoute, deleteRoute, className, size = 'lg' }: AvatarUploadProps) {
    const [isUploading, setIsUploading] = useState(false);
    const [isDeleting, setIsDeleting] = useState(false);
    const [showDeleteConfirm, setShowDeleteConfirm] = useState(false);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const file = e.target.files?.[0];
        if (!file) return;

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            toast.error('File size must be less than 2MB');
            return;
        }

        // Validate file type
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            toast.error('File must be a JPEG, PNG, or WebP image');
            return;
        }

        setIsUploading(true);

        router.post(
            uploadRoute,
            { avatar: file },
            {
                forceFormData: true,
                onFinish: () => {
                    setIsUploading(false);
                    if (fileInputRef.current) {
                        fileInputRef.current.value = '';
                    }
                },
            },
        );
    };

    const handleDelete = () => {
        setShowDeleteConfirm(true);
    };

    const handleConfirmDelete = () => {
        setIsDeleting(true);
        router.delete(deleteRoute, {
            onFinish: () => setIsDeleting(false),
        });
        setShowDeleteConfirm(false);
    };

    const handleUploadClick = () => {
        fileInputRef.current?.click();
    };

    const isLoading = isUploading || isDeleting;

    return (
        <div className={cn('flex flex-col items-center gap-4', className)}>
            <div className="relative">
                <Avatar className={cn(sizeClasses[size], 'border-muted border-2')}>
                    {isLoading ? (
                        <AvatarFallback>
                            <Loader2 className="text-muted-foreground h-8 w-8 animate-spin" />
                        </AvatarFallback>
                    ) : (
                        <>
                            <AvatarImage src={avatarUrl || undefined} alt={name} />
                            <AvatarFallback className="bg-neutral-200 text-lg text-black dark:bg-neutral-700 dark:text-white">
                                {getInitials(name)}
                            </AvatarFallback>
                        </>
                    )}
                </Avatar>

                {!isLoading && (
                    <Button
                        type="button"
                        variant="outline"
                        size="icon"
                        className="absolute -right-1 -bottom-1 h-8 w-8 rounded-full shadow-md"
                        onClick={handleUploadClick}
                    >
                        <Camera className="h-4 w-4" />
                        <span className="sr-only">Upload avatar</span>
                    </Button>
                )}
            </div>

            <input
                ref={fileInputRef}
                type="file"
                accept="image/jpeg,image/png,image/webp"
                className="hidden"
                onChange={handleFileChange}
                disabled={isLoading}
            />

            <div className="flex gap-2">
                <Button type="button" variant="outline" size="sm" onClick={handleUploadClick} disabled={isLoading}>
                    {isUploading ? (
                        <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Uploading...
                        </>
                    ) : (
                        <>
                            <Camera className="mr-2 h-4 w-4" />
                            {avatarUrl ? 'Change Photo' : 'Upload Photo'}
                        </>
                    )}
                </Button>

                {avatarUrl && (
                    <Button type="button" variant="outline" size="sm" onClick={handleDelete} disabled={isLoading}>
                        {isDeleting ? (
                            <>
                                <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                Removing...
                            </>
                        ) : (
                            <>
                                <Trash2 className="mr-2 h-4 w-4" />
                                Remove
                            </>
                        )}
                    </Button>
                )}
            </div>

            <p className="text-muted-foreground text-xs">Allowed: JPEG, PNG, WebP. Max size: 2MB</p>

            <DeleteConfirmation
                isOpen={showDeleteConfirm}
                onClose={() => setShowDeleteConfirm(false)}
                title="Remove Avatar"
                description="Are you sure you want to remove your avatar?"
                confirmText="Remove"
                variant="destructive"
                onConfirm={handleConfirmDelete}
                isDeleting={isDeleting}
            />
        </div>
    );
}
