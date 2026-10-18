import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/lib/initials';
import { type User } from '@/types';

export function UserInfo({ user, showEmail = false }: { user: User; showEmail?: boolean }) {
    return (
        <>
            <Avatar className="rounded-shape-full size-8 overflow-hidden">
                <AvatarImage src={user.avatar} alt={user.name} />
                <AvatarFallback className="rounded-shape-full bg-primary-container text-label-lg text-on-primary-container">
                    {getInitials(user.name)}
                </AvatarFallback>
            </Avatar>
            <div className="grid flex-1 text-left leading-tight">
                <span className="text-title-sm text-on-surface truncate">{user.name}</span>
                {showEmail && <span className="text-body-sm text-on-surface-variant truncate">{user.email}</span>}
            </div>
        </>
    );
}
