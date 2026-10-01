export function getInitials(fullName: string): string {
    const names = fullName.trim().split(' ');
    const firstName = names[0];
    const lastName = names[names.length - 1];

    if (names.length === 0 || !firstName) return '';
    if (names.length === 1) return firstName.charAt(0).toUpperCase();

    return `${firstName.charAt(0)}${lastName?.charAt(0) ?? ''}`.toUpperCase();
}
