import { resolveTopAppBar } from '@/lib/top-app-bar';
import { describe, expect, it } from 'vitest';

describe('resolveTopAppBar', () => {
    it('returns an empty title and no back target without breadcrumbs', () => {
        expect(resolveTopAppBar([])).toEqual({ title: '', backHref: null });
    });

    it('uses the only breadcrumb as the title on top-level pages', () => {
        expect(resolveTopAppBar([{ title: 'Users', href: '/users' }])).toEqual({ title: 'Users', backHref: null });
    });

    it('uses the last item as title and the parent href as back target', () => {
        expect(
            resolveTopAppBar([
                { title: 'Users', href: '/users' },
                { title: 'Edit', href: '#' },
            ]),
        ).toEqual({ title: 'Edit', backHref: '/users' });
    });

    it('goes back one level on deep pages', () => {
        expect(
            resolveTopAppBar([
                { title: 'Roles', href: '/roles' },
                { title: 'Editor', href: '/roles/5' },
                { title: 'Edit Permissions', href: '#' },
            ]),
        ).toEqual({ title: 'Edit Permissions', backHref: '/roles/5' });
    });

    it.each(['#', ''])('has no back target when the parent href is %j', (href) => {
        expect(
            resolveTopAppBar([
                { title: 'Calendar Events', href },
                { title: 'Add Event', href: '#' },
            ]).backHref,
        ).toBeNull();
    });
});
